# Update the bucket name if needed - make sure that you have the adequate rights to put files into that bucket 
# (check ec2instance role or your laptop local credentials)
BUCKETNAME="kinesis-datagen"

BASEDIR=$(dirname "$0")
cd $BASEDIR"/../"
echo "-> Creating tar package"
tar --exclude='.git' --exclude='vendor' -cvf /tmp/kinesis-datagen.tar ./

echo "-> Uploading cloudformation template to S3"
aws s3 cp $BASEDIR"/../aws/cloudformation.json" "s3://"$BUCKETNAME"/cloudformation.json"

echo "-> Uploading tar package to S3"
aws s3 cp /tmp/kinesis-datagen.tar "s3://"$BUCKETNAME"/kinesis-datagen.tar"
