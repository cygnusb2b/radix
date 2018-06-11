node {
  def phpBuilder = docker.image("scomm/php5.6:latest")
  def nodeBuilder = docker.image("scomm/node-build:latest")
  phpBuilder.pull()
  nodeBuilder.pull()

  // Test
  try {
    stage('Checkout') {
      checkout scm
    }

    nodeBuilder.inside("-v ${env.WORKSPACE}/src/AppBundle/Resources/radix:/var/www/html -u 0:0 --entrypoint=''") {
      stage('App Yarn') {
        sh "cd /var/www/html && yarn install --silent"
      }
      stage('App Bower') {
        sh "cd /var/www/html && bower install --quiet --allow-root"
      }
      stage('App Ember') {
        sh "cd /var/www/html && ember build --environment='production' --silent"
      }
      stage('App Cleanup') {
        sh "cd /var/www/html && rm -rf tmp node_modules bower_components"
      }
    }

    phpBuilder.inside("-v ${env.WORKSPACE}:/var/www/html -u 0:0 --entrypoint=''") {
      withEnv(['SYMFONY_ENV=test', 'APP_ENV=test']) {
        stage('Test Install') {
          withCredentials([usernamePassword(credentialsId: 'github-login-scommbot', passwordVariable: 'TOKEN', usernameVariable: 'USER')]) {
            sh "bin/composer config -g github-oauth.github.com $TOKEN"
          }
          sh "bin/composer install --no-interaction --prefer-dist"
        }
        stage('Test Assets') {
          sh "php bin/console assetic:dump --env=test --no-debug"
        }
        stage('Test Execute') {
          sh "bin/phpunit --log-junit unitTestReport.xml"
          junit "unitTestReport.xml"
        }
        stage('Cleanup') {
          sh "rm -rf app/config/parameters.yml"
        }
      }
    }
  } catch (e) {
    slackSend color: 'bad', message: "Failed testing ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
    throw e
  }

  if (env.BRANCH_NAME == 'master') {

    // Build
    try {
      stage('Build PHP') {
        withCredentials([file(credentialsId: 'radix.app.config.parameters.yml', variable: 'FILE')]) {
          sh "cp $FILE app/config/parameters.yml"
        }
        phpBuilder.inside("-v ${env.WORKSPACE}:/var/www/html -u 0:0 --entrypoint=''") {
          withEnv(['SYMFONY_ENV=prod', 'APP_ENV=prod']) {
            withCredentials([usernamePassword(credentialsId: 'github-login-scommbot', passwordVariable: 'TOKEN', usernameVariable: 'USER')]) {
              sh "bin/composer config -g github-oauth.github.com $TOKEN"
            }
            sh "rm -rf vendor/*"
            sh "sed -i.bak \'s/framework_version:.*/framework_version: ${env.BRANCH_NAME}_${env.BUILD_NUMBER}/g\' app/config/parameters.yml"
            sh "bin/composer install --optimize-autoloader --no-interaction --prefer-dist --no-dev --no-scripts"
          }
        }
      }
      stage('Build Container') {
        phpBuilder = docker.build("radix:v${env.BUILD_NUMBER}", ".")
      }
    } catch (e) {
      slackSend color: 'bad', message: "Failed building ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
      throw e
    }

    // Deploy
    try {
      stage('Deploy Image') {
        docker.withRegistry('https://664537616798.dkr.ecr.us-east-1.amazonaws.com', 'ecr:us-east-1:aws-jenkins-login') {
          phpBuilder.push("v${env.BUILD_NUMBER}");
        }
      }
      stage('Deploy Upgrade') {
        rancher confirm: true, credentialId: 'rancher', endpoint: 'https://rancher.as3.io/v2-beta', environmentId: '1a18', image: "664537616798.dkr.ecr.us-east-1.amazonaws.com/radix:v${env.BUILD_NUMBER}", service: 'radix/radix', environments: '', ports: '', timeout: 300
      }
      stage('Deploy Notify') {
        slackSend color: 'good', message: "Finished deploying ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
      }
    } catch (e) {
      slackSend color: 'bad', message: "Failed deploying ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
      throw e
    }

  }

}
