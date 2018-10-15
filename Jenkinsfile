pipeline {
    agent { 
        dockerfile true
    }
    stages {
        stage('prepare') {
            steps {
                sh 'git clone https://5eea726c4add0c37f390133c981f12e28c50d75e@github.com/BentleySystems/pwcm-legacy.git'
                sh 'ls -l'
                echo "Branch: ${env.BRANCH_NAME}"
            }
        }
    }
}
