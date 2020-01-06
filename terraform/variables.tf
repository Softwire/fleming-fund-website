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
variable "CLOUDFRONT_DOMAIN" {
  type = string
}
variable "CLOUDFRONT_ACM_CERTIFICATE_ARN" {
  type = string
}

variable "email" {
  type    = string
  default = "Team-FlemingFundSupport@softwire.com"
}

variable "eb_version_label" {
  type    = string
  default = "fleming-fund-stage-app-version_2019-12-18T09:48:37.522Z"
}

variable "eb_ec2_ssh_key_name" {
  type    = string
  default = "fleming-fund"
}
