

resource "aws_elastic_beanstalk_environment" "app-env" {
  name                = "FlemingEbStage-env" # qq
  application         = var.elastic_beanstalk_application_name
  solution_stack_name = "64bit Amazon Linux 2018.03 v2.8.1 running PHP 7.2"
  tier                = "WebServer"
  tags                = {
    environment = var.environment_tag
  }
  version_label       = var.eb_version_label

  dynamic "setting" {
    for_each = local.environment_variables
    content {
      name      = setting.key
      namespace = "aws:elasticbeanstalk:application:environment"
      value     = setting.value
    }
  }

  setting {
    name      = "Application Healthcheck URL"
    namespace = "aws:elasticbeanstalk:application"
    value     = ""
  }
  setting {
    name      = "AssociatePublicIpAddress"
    namespace = "aws:ec2:vpc"
    value     = "true"
  }
  setting {
    name      = "Automatically Terminate Unhealthy Instances"
    namespace = "aws:elasticbeanstalk:monitoring"
    value     = "true"
  }
  setting {
    name      = "Availability Zones"
    namespace = "aws:autoscaling:asg"
    value     = "Any"
  }
  setting {
    name      = "BatchSize"
    namespace = "aws:elasticbeanstalk:command"
    value     = "100"
  }
  setting {
    name      = "BatchSizeType"
    namespace = "aws:elasticbeanstalk:command"
    value     = "Percentage"
  }
  setting {
    name      = "ConfigDocument"
    namespace = "aws:elasticbeanstalk:healthreporting:system"
    value = jsonencode(
      {
        CloudWatchMetrics = {
          Environment = {
            ApplicationLatencyP10     = null
            ApplicationLatencyP50     = null
            ApplicationLatencyP75     = null
            ApplicationLatencyP85     = null
            ApplicationLatencyP90     = null
            ApplicationLatencyP95     = null
            ApplicationLatencyP99     = null
            "ApplicationLatencyP99.9" = null
            ApplicationRequests2xx    = null
            ApplicationRequests3xx    = null
            ApplicationRequests4xx    = null
            ApplicationRequests5xx    = null
            ApplicationRequestsTotal  = null
            InstancesDegraded         = null
            InstancesInfo             = null
            InstancesNoData           = null
            InstancesOk               = null
            InstancesPending          = null
            InstancesSevere           = null
            InstancesUnknown          = null
            InstancesWarning          = null
          }
          Instance = {
            ApplicationLatencyP10     = null
            ApplicationLatencyP50     = null
            ApplicationLatencyP75     = null
            ApplicationLatencyP85     = null
            ApplicationLatencyP90     = null
            ApplicationLatencyP95     = null
            ApplicationLatencyP99     = null
            "ApplicationLatencyP99.9" = null
            ApplicationRequests2xx    = null
            ApplicationRequests3xx    = null
            ApplicationRequests4xx    = null
            ApplicationRequests5xx    = null
            ApplicationRequestsTotal  = null
            CPUIdle                   = null
            CPUIowait                 = null
            CPUIrq                    = null
            CPUNice                   = null
            CPUSoftirq                = null
            CPUSystem                 = null
            CPUUser                   = null
            InstanceHealth            = null
            LoadAverage1min           = null
            LoadAverage5min           = null
            RootFilesystemUtil        = null
          }
        }
        Rules = {
          Environment = {
            Application = {
              ApplicationRequests4xx = {
                Enabled = true
              }
            }
          }
        }
        Version = 1
      }
    )
  }
  setting {
    name      = "DefaultSSHPort"
    namespace = "aws:elasticbeanstalk:control"
    value     = "22"
  }
  setting {
    name      = "DeleteOnTerminate"
    namespace = "aws:elasticbeanstalk:cloudwatch:logs"
    value     = "false"
  }
  setting {
    name      = "DeleteOnTerminate"
    namespace = "aws:elasticbeanstalk:cloudwatch:logs:health"
    value     = "false"
  }
  setting {
    name      = "DeploymentPolicy"
    namespace = "aws:elasticbeanstalk:command"
    value     = "AllAtOnce"
  }
  setting {
    name      = "EC2KeyName"
    namespace = "aws:autoscaling:launchconfiguration"
    value     = var.eb_ec2_ssh_key_name
  }
  setting {
    name      = "ELBScheme"
    namespace = "aws:ec2:vpc"
    value     = "public"
  }
  setting {
    name      = "ELBSubnets"
    namespace = "aws:ec2:vpc"
    value     = var.vpc_public_subnet_id
  }
  setting {
    name      = "EnableSpot"
    namespace = "aws:ec2:instances"
    value     = "false"
  }
  setting {
    name      = "EnvironmentType"
    namespace = "aws:elasticbeanstalk:environment"
    value     = "SingleInstance"
  }
  setting {
    name      = "HealthCheckSuccessThreshold"
    namespace = "aws:elasticbeanstalk:healthreporting:system"
    value     = "Ok"
  }
  setting {
    name      = "HealthStreamingEnabled"
    namespace = "aws:elasticbeanstalk:cloudwatch:logs:health"
    value     = "false"
  }
  setting {
    name      = "IamInstanceProfile"
    namespace = "aws:autoscaling:launchconfiguration"
    value     = "aws-elasticbeanstalk-ec2-role"
  }
  setting {
    name      = "IgnoreHealthCheck"
    namespace = "aws:elasticbeanstalk:command"
    value     = "false"
  }
  setting {
    name      = "ImageId"
    namespace = "aws:autoscaling:launchconfiguration"
    value     = "ami-09660e22496a6b302"
  }
  setting {
    name      = "InstanceRefreshEnabled"
    namespace = "aws:elasticbeanstalk:managedactions:platformupdate"
    value     = "false"
  }
  setting {
    name      = "InstanceType"
    namespace = "aws:autoscaling:launchconfiguration"
    value     = "t2.micro"
  }
  setting {
    name      = "InstanceTypes"
    namespace = "aws:ec2:instances"
    value     = "t2.micro"
  }
  setting {
    name      = "LaunchTimeout"
    namespace = "aws:elasticbeanstalk:control"
    value     = "0"
  }
  setting {
    name      = "LaunchType"
    namespace = "aws:elasticbeanstalk:control"
    value     = "Migration"
  }
  setting {
    name      = "LogPublicationControl"
    namespace = "aws:elasticbeanstalk:hostmanager"
    value     = "false"
  }
  setting {
    name      = "ManagedActionsEnabled"
    namespace = "aws:elasticbeanstalk:managedactions"
    value     = "false"
  }
  setting {
    name      = "MaxBatchSize"
    namespace = "aws:autoscaling:updatepolicy:rollingupdate"
    value     = ""
  }
  setting {
    name      = "MaxSize"
    namespace = "aws:autoscaling:asg"
    value     = "1"
  }
  setting {
    name      = "MinInstancesInService"
    namespace = "aws:autoscaling:updatepolicy:rollingupdate"
    value     = ""
  }
  setting {
    name      = "MinSize"
    namespace = "aws:autoscaling:asg"
    value     = "1"
  }
  setting {
    name      = "MonitoringInterval"
    namespace = "aws:autoscaling:launchconfiguration"
    value     = "5 minute"
  }
  setting {
    name      = "Notification Endpoint"
    namespace = "aws:elasticbeanstalk:sns:topics"
    value     = var.email
  }
  setting {
    name      = "Notification Protocol"
    namespace = "aws:elasticbeanstalk:sns:topics"
    value     = "email"
  }
  setting {
    name      = "RetentionInDays"
    namespace = "aws:elasticbeanstalk:cloudwatch:logs"
    value     = "7"
  }
  setting {
    name      = "RetentionInDays"
    namespace = "aws:elasticbeanstalk:cloudwatch:logs:health"
    value     = "7"
  }
  setting {
    name      = "RollbackLaunchOnFailure"
    namespace = "aws:elasticbeanstalk:control"
    value     = "false"
  }
  setting {
    name      = "RollingUpdateEnabled"
    namespace = "aws:autoscaling:updatepolicy:rollingupdate"
    value     = "false"
  }
  setting {
    name      = "RollingUpdateType"
    namespace = "aws:autoscaling:updatepolicy:rollingupdate"
    value     = "Time"
  }
  setting {
    name      = "SSHSourceRestriction"
    namespace = "aws:autoscaling:launchconfiguration"
    value     = "tcp,22,22,0.0.0.0/0"
  }
  setting {
    name      = "SecurityGroups"
    namespace = "aws:autoscaling:launchconfiguration"
    value     = var.webserver_sg_id
  }
  setting {
    name      = "ServiceRole"
    namespace = "aws:elasticbeanstalk:environment"
    value     = "aws-elasticbeanstalk-service-role"
  }
  setting {
    name      = "SpotFleetOnDemandAboveBasePercentage"
    namespace = "aws:ec2:instances"
    value     = "0"
  }
  setting {
    name      = "SpotFleetOnDemandBase"
    namespace = "aws:ec2:instances"
    value     = "0"
  }
  setting {
    name      = "StreamLogs"
    namespace = "aws:elasticbeanstalk:cloudwatch:logs"
    value     = "false"
  }
  setting {
    name      = "Subnets"
    namespace = "aws:ec2:vpc"
    value     = var.vpc_public_subnet_id
  }
  setting {
    name      = "SystemType"
    namespace = "aws:elasticbeanstalk:healthreporting:system"
    value     = "basic"
  }
  setting {
    name      = "Timeout"
    namespace = "aws:autoscaling:updatepolicy:rollingupdate"
    value     = "PT30M"
  }
  setting {
    name      = "Timeout"
    namespace = "aws:elasticbeanstalk:command"
    value     = "600"
  }
  setting {
    name      = "VPCId"
    namespace = "aws:ec2:vpc"
    value     = var.vpc_id
  }
  setting {
    name      = "allow_url_fopen"
    namespace = "aws:elasticbeanstalk:container:php:phpini"
    value     = "On"
  }
  setting {
    name      = "composer_options"
    namespace = "aws:elasticbeanstalk:container:php:phpini"
    value     = ""
  }
  setting {
    name      = "display_errors"
    namespace = "aws:elasticbeanstalk:container:php:phpini"
    value     = "Off"
  }
  setting {
    name      = "document_root"
    namespace = "aws:elasticbeanstalk:container:php:phpini"
    value     = ""
  }
  setting {
    name      = "max_execution_time"
    namespace = "aws:elasticbeanstalk:container:php:phpini"
    value     = "60"
  }
  setting {
    name      = "memory_limit"
    namespace = "aws:elasticbeanstalk:container:php:phpini"
    value     = "256M"
  }
  setting {
    name      = "zlib.output_compression"
    namespace = "aws:elasticbeanstalk:container:php:phpini"
    value     = "Off"
  }
}
