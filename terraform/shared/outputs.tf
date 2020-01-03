output "vpc_id" {
  value = aws_vpc.main.id
}

output "elastic_beanstalk_application_name" {
  value = aws_elastic_beanstalk_application.app.name
}

output "vpc_public_subnet_id" {
  value = aws_subnet.vpc-public-subnet.id
}
