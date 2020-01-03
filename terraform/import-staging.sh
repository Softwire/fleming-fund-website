#!/usr/bin/env bash

terraform import module.shared.aws_vpc.main vpc-02a0c6b651aae14cf
terraform import module.shared.aws_subnet.vpc-public-subnet subnet-0018d6b3b7c9aa4ab
terraform import module.shared.aws_subnet.vpc-unused-subnet subnet-03c91d2a5f257fedf
terraform import module.shared.aws_route_table.main rtb-09552e3d23e9e140e
terraform import module.shared.aws_route_table.public-subnet rtb-0c3f1e80e58d7c16d
terraform import module.shared.aws_internet_gateway.main igw-002ff6745e49bb994
terraform import module.shared.aws_route_table_association.public-subnet subnet-0018d6b3b7c9aa4ab/rtb-0c3f1e80e58d7c16d
terraform import module.shared.aws_network_acl.unused-subnet-acl acl-0f5c72997392b70b8

terraform import module.shared.aws_elastic_beanstalk_application.app fleming-eb-stage

terraform import aws_elastic_beanstalk_environment.app-env e-ptv9rimnn3

terraform import aws_efs_file_system.wp-uploads fs-bcd88175
