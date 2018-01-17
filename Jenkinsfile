node {
    try {
      stage('Checkout') {
        checkout scm
      }
      stage('Test') {
        docker.withRegistry('https://registry.hub.docker.com', 'docker-registry-login') {
          def tester = docker.image("limit0/php56:latest")
          tester.pull()
          tester.inside("-v ${env.WORKSPACE}:/var/www/html -u 0:0") {
              withEnv(['SYMFONY_ENV=test', 'APP_ENV=test']) {
                sh 'php bin/composer install --no-interaction --prefer-dist'
                sh 'php bin/console assetic:dump --env=test'
                sh 'php bin/phpunit'
              }
            }
          }
        }
    } catch (e) {
      slackSend channel: '@solocommand', color: 'bad', message: "Failed testing ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
      process.exit(1)
    }

    try {
      docker.withRegistry('https://registry.hub.docker.com', 'docker-registry-login') {
        def builder = docker.image('limit0/php56:latest')
        def nodeBuilder = docker.image('limit0/node-build:latest')
        builder.pull()
        nodeBuilder.pull()

        stage('Composer') {
          builder.inside("-v ${env.WORKSPACE}:/var/www/html -u 0:0") {
            withEnv(['SYMFONY_ENV=prod', 'APP_ENV=prod']) {
                // Reset cache for production build
                sh 'rm -rf var/cache/*';
                sh "sed -i.bak \'s/framework_version:.*/framework_version: ${env.BRANCH_NAME}_${env.BUILD_NUMBER}/g\' app/config/parameters.yml"
                sh 'php bin/composer install --optimize-autoloader --no-interaction --prefer-dist --no-dev'
              }
            }
        }

        stage('Ember') {
          nodeBuilder.inside("-v ${env.WORKSPACE}:/var/www/html -u 0:0") {
            sh 'cd src/AppBundle/Resources/radix && npm install --silent';
            sh 'cd src/AppBundle/Resources/radix && bower install --quiet --allow-root'
            sh 'cd src/AppBundle/Resources/radix && ember build --silent --environment=production'
            sh 'cd src/AppBundle/Resources/radix && rm -rf tmp node_modules bower_components'
          }
        }

        stage ('Cache & Assets') {
          builder.inside("-v ${env.WORKSPACE}:/var/www/html -u 0:0") {
            withEnv(['SYMFONY_ENV=prod', 'APP_ENV=prod']) {
              sh 'php bin/console cache:warm --env=prod'
              sh 'php bin/console assetic:dump --env=prod'
            }
          }
        }
      }
    } catch (e) {
      slackSend channel: '@solocommand', color: 'bad', message: "Failed building ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
      process.exit(1)
    }

    if (!env.BRANCH_NAME.contains('PR-')) {
      try {
        stage('Build Container') {
          docker.withRegistry('https://664537616798.dkr.ecr.us-east-1.amazonaws.com', 'ecr:us-east-1:aws-jenkins-login') {
            myDocker = docker.build("radix-server:v${env.BUILD_NUMBER}", '.')
            myDocker.push("v${env.BUILD_NUMBER}");
          }
        }
        stage('Upgrade Container') {
          rancher confirm: true, credentialId: 'rancher', endpoint: 'https://rancher.as3.io/v2-beta', environmentId: '1a18', image: "664537616798.dkr.ecr.us-east-1.amazonaws.com/radix-server:v${env.BUILD_NUMBER}", service: 'radix/server', environments: '', ports: '', timeout: 30
        }
        stage('Notify Upgrade') {
          slackSend channel: '@solocommand', color: 'good', message: "Finished deploying ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
        }
      } catch (e) {
        slackSend channel: '@solocommand', color: 'bad', message: "Failed deploying ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
        process.exit(1)
      }
    }
}
