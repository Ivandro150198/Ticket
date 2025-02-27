pipeline {
    agent { docker { image 'maven:3.8.4-openjdk-17' } }
    stages {
        stage('Build') {
            steps {
                sh 'mvn clean install'
            }
        }
        stage('Test') {
            steps {
                sh 'mvn test'
            }
        }
        stage('Deploy') {
            steps {
                sh 'docker build -t meu-projeto:latest .'
                sh 'docker run -d -p 8080:8080 meu-projeto:latest'
            }
        }
    }
}
