variable "name_prefix" {
  description = "Prefix for all AWS resource names"
  type        = string
  default     = "fleming-fund-stage"
}

variable "ip_whitelist_map" {
  description = "CIDR blocks to allow SSH access"
  type = list(object({
    rule_no = number
    cidr    = string
  }))
  default = [
    {
      rule_no = 22
      cidr    = "212.159.19.168/32"
    },
    {
      rule_no = 23
      cidr    = "31.221.86.178/32"
    },
    {
      rule_no = 24
      cidr    = "167.98.33.82/32"
    },
    {
      rule_no = 25
      cidr    = "82.163.115.98/32"
    },
    {
      rule_no = 26
      cidr    = "87.224.105.250/32"
    },
  ]
}

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
  type    = string
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

locals {
  environment_variables = {
    AUTH_KEY         = var.AUTH_KEY
    AUTH_SALT        = var.AUTH_SALT
    CLOUDFRONT_HOME  = var.CLOUDFRONT_HOME
    DB_HOST          = "fleming-fund-stage-db-1.cfcbbvem75td.eu-west-1.rds.amazonaws.com"
    DB_NAME          = "wordpress"
    DB_PASSWORD      = var.DB_PASSWORD
    DB_USER          = var.DB_USER
    DOMAIN           = var.DOMAIN
    EFS_NAME         = aws_efs_file_system.wp-uploads.dns_name
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

variable "email" {
  type = string
  default = "Team-FlemingFundSupport@softwire.com"
}
