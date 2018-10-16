pipeline {
    agent { 
        dockerfile true
    }
    stages {
        stage('bootstrap') {
            steps {
                deleteDir path: 'src'
                deleteDir path: 'dist'
                sh 'mkdir src'
                sh 'mkdir dist'
                sh 'ls -l'
                dir 'src'
                git branch: 'master',
                    credentialsId: '4ccd37ee-ad94-475c-a0ff-b545e70cbaed',
                    url: 'https://github.com/BentleySystems/pwcm-legacy.git'

                sh 'ls -l'
                echo "Branch: ${env.BRANCH_NAME}"
            }
        }
        stage('test') {
            steps {
                sh 'Tests OK!'
            }
        }
    }
}
