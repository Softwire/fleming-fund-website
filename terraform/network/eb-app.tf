
data "aws_iam_role" "beanstalk_service" {
  name = "aws-elasticbeanstalk-service-role"
}

resource "aws_elastic_beanstalk_application" "app" {
  name        = "fleming-eb-stage" # qq "${var.name_prefix}-app"
  description = "${var.name_prefix} EB application"

  tags = {
    environment = var.environment_tag
  }

  appversion_lifecycle {
    service_role          = data.aws_iam_role.beanstalk_service.arn
    max_count             = 100
    delete_source_from_s3 = false
    max_age_in_days = 0
  }
}
