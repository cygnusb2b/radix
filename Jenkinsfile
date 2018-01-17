node {
  dir("${env.BUILD_NUMBER}") {
    try {
      stage('Checkout') {
        checkout scm
      }
      docker.withRegistry('https://registry.hub.docker.com', 'docker-registry-login') {
        def tester = docker.image("limit0/php56:latest")
        tester.pull()
        tester.inside("-v ${env.WORKSPACE}:/var/www/html -u 0:0") {
          stage('Testing') {
            withEnv(['SYMFONY_ENV=test', 'APP_ENV=test']) {
              sh 'php bin/composer install --no-interaction --prefer-dist'
              sh 'php bin/console assetic:dump --env=test'
              sh 'php bin/phpunit'
            }
          }
        }
      }

    } catch (e) {
      slackSend color: 'bad', message: "Failed testing ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
      process.exit(1)
    }

    try {
      docker.withRegistry('https://registry.hub.docker.com', 'docker-registry-login') {
        def builder = docker.image('scomm/php5.6:latest')
        builder.pull()
        builder.inside('-v ${env.WORKSPACE}/${env.BUILD_NUMBER}:/var/www/html -u 0:0') {
          withEnv(['SYMFONY_ENV=prod', 'APP_ENV=prod']) {

            stage('Install') {
              // Reset cache & vendor for production build
              sh 'rm -rf var/cache/*';
              sh "sed -i.bak \'s/framework_version:.*/framework_version: ${env.BRANCH_NAME}_${env.BUILD_NUMBER}/g\' app/config/parameters.yml"
              sh 'php bin/composer install --optimize-autoloader --no-interaction --prefer-dist --no-dev'
            }

            stage('Install Ember') {
              dir('src/AppBundle/Resources/radix') {
                sh 'npm install'
                sh 'bower install --quiet --allow-root'
                sh 'ember build --environment=production'
              }
            }

            stage('Cache Warmup') {
              sh 'php bin/console cache:warm --env=prod'
            }

            stage('Assetic Dump') {
              sh 'php bin/console assetic:dump --env=prod'
            }

          }
        }
      }
    } catch (e) {
      slackSend color: 'bad', message: "Failed building ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
      process.exit(1)
    }

    if (!env.BRANCH_NAME.contains('PR-')) {
      try {
        stage('Create Deployable') {
          sh 'pwd > buildpath'
          step([$class: 'ArtifactArchiver', artifacts: 'buildpath'])
          sh 'tar --exclude=deployable.tar.gz -zcf deployable.tar.gz *'
          step([$class: 'ArtifactArchiver', artifacts: 'deployable.tar.gz'])
        }
        docker.withRegistry('https://664537616798.dkr.ecr.us-east-1.amazonaws.com', 'ecr:us-east-1:aws-jenkins-login') {
          stage('Build Container') {
            myDocker = docker.build("radix:v${env.BUILD_NUMBER}", '.')
          }
          stage('Push Container') {
            myDocker.push("latest");
            myDocker.push("v${env.BUILD_NUMBER}");
          }
        }
        stage('Upgrade Container') {
          rancher confirm: true, credentialId: 'rancher', endpoint: 'https://rancher.as3.io/v2-beta', environmentId: '1a18', image: "664537616798.dkr.ecr.us-east-1.amazonaws.com/radix:v${env.BUILD_NUMBER}", service: 'radix/server', environments: '', ports: '', timeout: 30
        }
        stage('Notify Upgrade') {
          slackSend color: 'good', message: "Finished deploying ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
        }
      } catch (e) {
        slackSend color: 'bad', message: "Failed deploying ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
        process.exit(1)
      }
    }
  }
}
