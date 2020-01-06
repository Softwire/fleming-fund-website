provider "aws" {
  profile = "default"
  region  = "eu-west-1"
}

terraform {
  backend "s3" {
    bucket = "fleming-fund-terraform-state"
    key    = "vpc-state"
    region = "eu-west-1"
  }
}

module "network" {
  source           = "./network"
  name_prefix      = var.name_prefix
  ip_whitelist_map = var.ip_whitelist_map
}

module "app" {
  source                             = "./app"
  name_prefix                        = var.name_prefix
  ip_whitelist_map                   = var.ip_whitelist_map
  email                              = var.email
  eb_version_label                   = var.EB_VERSION_LABEL
  eb_ec2_ssh_key_name                = var.eb_ec2_ssh_key_name
  vpc_id                             = module.network.vpc_id
  elastic_beanstalk_application_name = module.network.elastic_beanstalk_application_name
  vpc_public_subnet_id               = module.network.vpc_public_subnet_id
  vpc_unused_subnet_id               = module.network.vpc_unused_subnet_id
  webserver_sg_id                    = module.network.webserver_sg_id
  efs_sg_id                          = module.network.efs_sg_id
  db_sg_id                           = module.network.db_sg_id
  FLEM_ENV                           = var.FLEM_ENV
  DB_PASSWORD                        = var.DB_PASSWORD
  DB_USER                            = var.DB_USER
  WP_HOME                            = var.WP_HOME
  DOMAIN                             = var.DOMAIN
  CLOUDFRONT_HOME                    = var.CLOUDFRONT_HOME
  AUTH_KEY                           = var.AUTH_KEY
  AUTH_SALT                          = var.AUTH_SALT
  LOGGED_IN_KEY                      = var.LOGGED_IN_KEY
  LOGGED_IN_SALT                     = var.LOGGED_IN_SALT
  NONCE_KEY                          = var.NONCE_KEY
  NONCE_SALT                         = var.NONCE_SALT
  SECURE_AUTH_KEY                    = var.SECURE_AUTH_KEY
  SECURE_AUTH_SALT                   = var.SECURE_AUTH_SALT
}

module "cloudfront" {
  source                    = "./cloudfront"
  name_prefix               = var.name_prefix
  is_production             = var.FLEM_ENV == true
  cloudfront_domain_aliases = [var.CLOUDFRONT_DOMAIN]
  origin_domain             = var.DOMAIN
  acm_certificate_arn       = var.CLOUDFRONT_ACM_CERTIFICATE_ARN
}
