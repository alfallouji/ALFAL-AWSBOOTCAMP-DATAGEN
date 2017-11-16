TEMPLATE_URL="http://s3.amazonaws.com/kinesis-datagen/cloudformation.json"
KEY_NAME="my-keyname"
S3_BUCKET_NAME="kinesis-datagen"
PARAMETERS="ParameterKey=InstanceType,ParameterValue=m1.small ParameterKey=KeyName,ParameterValue="$KEY_NAME" ParameterKey=S3BucketName,ParameterValue="$S3_BUCKET_NAME" ParameterKey=SSHLocation,ParameterValue=0.0.0.0/0"
STACKNAME="test-kinesis-"$RANDOM

echo "Creating "$STACKNAME
aws cloudformation create-stack --stack-name $STACKNAME --template-url $TEMPLATE_URL --parameters $PARAMETERS --capabilities CAPABILITY_IAM --on-failure DO_NOTHING
