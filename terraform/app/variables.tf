variable "FLEM_ENV" {
  type = string
}
variable "DB_PASSWORD" {
  type = string
}
variable "DB_USER" {
  type = string
}
variable "WP_HOME" {
  type = string
}
variable "DOMAIN" {
  type = string
}
variable "CLOUDFRONT_HOME" {
  type = string
}
variable "AUTH_KEY" {
  type = string
}
variable "AUTH_SALT" {
  type = string
}
variable "LOGGED_IN_KEY" {
  type = string
}
variable "LOGGED_IN_SALT" {
  type = string
}
variable "NONCE_KEY" {
  type = string
}
variable "NONCE_SALT" {
  type = string
}
variable "SECURE_AUTH_KEY" {
  type = string
}
variable "SECURE_AUTH_SALT" {
  type = string
}

variable "name_prefix" {
  description = "Prefix for all AWS resource names"
  type        = string
}

variable "environment_tag" {
  type = string
}

variable "ip_whitelist_map" {
  description = "CIDR blocks to allow SSH access"
  type = list(object({
    rule_no = number
    cidr    = string
  }))
}

variable "email" {
  type = string
}

variable "eb_version_label" {
  type = string
}

variable "eb_ec2_ssh_key_name" {
  type = string
}

variable "vpc_id" {
  type = string
}

variable "elastic_beanstalk_application_name" {
  type = string
}

variable "vpc_public_subnet_id" {
  type = string
}

variable "vpc_unused_subnet_id" {
  type = string
}

variable "webserver_sg_id" {
  type = string
}

variable "efs_sg_id" {
  type = string
}

variable "db_sg_id" {
  type = string
}

locals {
  environment_variables = {
    AUTH_KEY         = var.AUTH_KEY
    AUTH_SALT        = var.AUTH_SALT
    CLOUDFRONT_HOME  = var.CLOUDFRONT_HOME
    DB_HOST          = aws_db_instance.db.address
    DB_NAME          = "wordpress"
    DB_PASSWORD      = var.DB_PASSWORD
    DB_USER          = var.DB_USER
    DOMAIN           = var.DOMAIN
    EFS_NAME         = aws_efs_mount_target.wp-uploads-webserver.dns_name
    FLEM_ENV         = var.FLEM_ENV
    LOGGED_IN_KEY    = var.LOGGED_IN_KEY
    LOGGED_IN_SALT   = var.LOGGED_IN_SALT
    NONCE_KEY        = var.NONCE_KEY
    NONCE_SALT       = var.NONCE_SALT
    SECURE_AUTH_KEY  = var.SECURE_AUTH_KEY
    SECURE_AUTH_SALT = var.SECURE_AUTH_SALT
    WP_HOME          = var.WP_HOME
  }
}
