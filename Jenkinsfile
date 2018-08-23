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

    nodeBuilder.inside("-v ${env.WORKSPACE}/admin:/var/www/html -u 0:0 --entrypoint=''") {
      stage('Build App') {
        sh "cd /var/www/html && yarn install --silent"
        sh "npm install -g bower && cd /var/www/html && bower install --quiet --allow-root"
      }
      stage('Test App') {
        sh "cd /var/www/html && ember build"
      }
    }

    phpBuilder.inside("-v ${env.WORKSPACE}/server:/var/www/html -u 0:0 --entrypoint=''") {
      withEnv(['SYMFONY_ENV=test', 'APP_ENV=test']) {
        stage('Build Server') {
          withCredentials([usernamePassword(credentialsId: 'github-login-scommbot', passwordVariable: 'TOKEN', usernameVariable: 'USER')]) {
            sh "bin/composer config -g github-oauth.github.com $TOKEN"
          }
          sh "bin/composer install --no-interaction"
        }
        stage('Test Server') {
          sh "bin/phpunit --log-junit unitTestReport.xml"
          junit "unitTestReport.xml"
        }
      }
    }
  } catch (e) {
    slackSend color: 'bad', message: "Failed testing ${env.JOB_NAME} #${env.BUILD_NUMBER} (<${env.BUILD_URL}|View>)"
    throw e
  }
}
