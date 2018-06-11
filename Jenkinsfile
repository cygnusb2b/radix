node {
    try {
        dir("${env.BUILD_NUMBER}") {
            docker.withRegistry('https://registry.hub.docker.com', 'docker-registry-login') {
                stage('Checkout') {
                    checkout changelog: false, poll: false, scm: [$class: "GitSCM", branches: [[name: "*/master"]], browser: [$class: "GithubWeb"], doGenerateSubmoduleConfigurations: false, extensions: [], submoduleCfg: [], userRemoteConfigs: [[credentialsId: "github-login", url: "https://github.com/cygnusb2b/radix/"]]]
                }
                def myDocker = docker.image("scomm/php5.6:latest")
                myDocker.pull()
                myDocker.inside("-v ${env.WORKSPACE}/${env.BUILD_NUMBER}:/var/www/html -u 0:0") {

                    withEnv(['SYMFONY_ENV=test', 'APP_ENV=test']) {

                        stage("Composer Install") {
                            withCredentials([usernamePassword(credentialsId: 'github-login-scommbot', passwordVariable: 'TOKEN', usernameVariable: 'USER')]) {
                              sh "bin/composer config -g github-oauth.github.com $TOKEN"
                            }
                            sh "composer install --no-interaction --prefer-dist"
                            sh "php bin/console assetic:dump --env=test"
                        }

                        stage("PHP Unit Test") {
                            sh "phpunit"
                        }
                    }

                    withEnv(['SYMFONY_ENV=prod', 'APP_ENV=prod']) {

                        stage("Prod Composer Install") {
                            sh "rm -fr var/cache/* vendor/* app/config/parameters.yml"
                            withCredentials([file(credentialsId: 'radix.app.config.parameters.yml', variable: 'FILE')]) {
                              sh "cp $FILE app/config/parameters.yml"
                            }
                            sh "composer install --optimize-autoloader --no-interaction --prefer-dist --no-dev"
                            sh "sed -i.bak \'s/framework_version:.*/framework_version: ${env.BRANCH_NAME}_${env.BUILD_NUMBER}/g\' app/config/parameters.yml"
                        }

                        stage("Install Ember") {
                            sh "cd src/AppBundle/Resources/radix/ && \
                            npm install --silent && \
                            bower install --quiet --allow-root && \
                            ember build --environment='production'"
                        }

                        stage("Cache Warmup") {
                            sh "php bin/console cache:warm --env=prod --no-debug"
                        }

                        stage("Assets Install") {
                            sh "php bin/console assets:install --env=prod"
                        }

                        stage("Assetic Dump") {
                            sh "php bin/console assetic:dump --env=prod --no-debug"
                        }

                    }

                }

            }

            stage("Create Deployable") {
                sh "pwd > buildpath"
                step([$class: 'ArtifactArchiver', artifacts: 'buildpath'])
                sh "tar --exclude=deployable.tar.gz -zcf deployable.tar.gz *"
                step([$class: 'ArtifactArchiver', artifacts: 'deployable.tar.gz'])
            }
        }

    } catch(e) {
        slackSend (color: '#FF0000', message: "FAILED: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]' (${env.BUILD_URL})")
        currentBuild.result = "FAILED"
        throw e
    }
}
