#!/usr/bin/env bash

terraform import module.network.aws_vpc.main vpc-02a0c6b651aae14cf
terraform import module.network.aws_subnet.vpc-public-subnet subnet-0018d6b3b7c9aa4ab
terraform import module.network.aws_subnet.vpc-unused-subnet subnet-03c91d2a5f257fedf
terraform import module.network.aws_route_table.main rtb-09552e3d23e9e140e
terraform import module.network.aws_route_table.public-subnet rtb-0c3f1e80e58d7c16d
terraform import module.network.aws_internet_gateway.main igw-002ff6745e49bb994
terraform import module.network.aws_route_table_association.public-subnet subnet-0018d6b3b7c9aa4ab/rtb-0c3f1e80e58d7c16d
terraform import module.network.aws_network_acl.unused-subnet-acl acl-0f5c72997392b70b8

terraform import module.network.aws_security_group.webserver sg-03d7b76a0fc740586
terraform import module.network.aws_security_group.efs sg-036541a1c6bf2d6d3
terraform import module.network.aws_security_group.db sg-040b987bf3e431184

terraform import module.network.aws_elastic_beanstalk_application.app fleming-eb-stage

terraform import module.app.aws_elastic_beanstalk_environment.app-env e-ptv9rimnn3

terraform import module.app.aws_efs_file_system.wp-uploads fs-bcd88175
terraform import module.app.aws_efs_mount_target.wp-uploads-webserver fsmt-e4374c2d

terraform import module.app.aws_db_instance.db fleming-fund-stage-db-1
terraform import module.app.aws_db_subnet_group.db-subnet default-vpc-02a0c6b651aae14cf

terraform import module.cloudfront.aws_cloudfront_distribution.cf E10JKI79ED809L
terraform import module.cloudfront.aws_s3_bucket.static-site-errors fleming-fund-stage-static-site-errors
