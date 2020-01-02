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

module "shared" {
  source           = "./shared"
  name_prefix      = var.name_prefix
  ip_whitelist_map = var.ip_whitelist_map
}
