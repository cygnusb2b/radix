node {
  def phpBuilder = docker.image("danlynn/ember-cli:2.11.1-node_6.10")
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
        sh "cd /var/www/html && ./install.sh"
      }
      stage('Test App') {
        sh "cd /var/www/html && ember test"
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
