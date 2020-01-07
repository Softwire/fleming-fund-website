variable "name_prefix" {
  description = "Prefix for all AWS resource names"
  type        = string
}

variable "ip_whitelist_map" {
  description = "CIDR blocks to allow SSH access"
  type = list(object({
    rule_no = number
    cidr    = string
  }))
}

variable "environment_tag" {
  type = string
}
