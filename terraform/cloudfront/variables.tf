variable "name_prefix" {
  description = "Prefix for all AWS resource names"
  type        = string
}

variable "is_production" {
  description = "Affects the price class of the CloudFront Distribution"
  type        = bool
}

variable "cloudfront_domain_aliases" {
  type = list(string)
}

variable "origin_domain" {
  type = string
}

variable "acm_certificate_arn" {
  type = string
}

locals {
  static_site_error_codes = [500, 501, 502, 503, 504]
}
