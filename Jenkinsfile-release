pipeline {
    agent { 
        docker { image 'php:7.0-cli' }
    }
    stages {
        stage('build') {
            steps {
                sh 'php --version'
                echo "Branch: ${env.BRANCH_NAME}"
            }
        }
    }
}
