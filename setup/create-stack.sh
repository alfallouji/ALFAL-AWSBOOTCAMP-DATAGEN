STACKNAME="test-kinesis-"$RANDOM
echo "Creating "$STACKNAME
aws cloudformation create-stack --stack-name $STACKNAME --template-url https://s3.amazonaws.com/kinesis-datagen/cloudformation.json --parameters ParameterKey=InstanceType,ParameterValue=m1.small ParameterKey=KeyName,ParameterValue=amazon-bashar ParameterKey=S3BucketName,ParameterValue=kinesis-datagen ParameterKey=SSHLocation,ParameterValue=0.0.0.0/0 --capabilities CAPABILITY_IAM
