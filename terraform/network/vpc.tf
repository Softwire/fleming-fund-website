
variable "vpc_cidr" {
  description = "CIDR for the whole VPC"
  default     = "10.120.0.0/16"
}

variable "public_subnet_cidr" {
  description = "CIDR for the Public Subnet"
  default     = "10.120.0.0/24"
}

variable "private_subnet_cidr" {
  description = "CIDR for the Private Subnet"
  default     = "10.120.1.0/24"
}

variable "unused_subnet_cidr" {
  description = "CIDR for the Unused Subnet"
  default     = "10.120.255.0/24"
}

resource "aws_vpc" "main" {
  cidr_block = "10.120.0.0/16"
  tags = {
    Name = "${var.name_prefix}-vpc"
  }
}

resource "aws_internet_gateway" "main" {
  vpc_id = aws_vpc.main.id
  tags = {
    "Name" = "${var.name_prefix}-internet-gateway"
  }
}

resource "aws_route_table" "main" {
  vpc_id = aws_vpc.main.id
  tags = {
    Name = "${var.name_prefix}-main-route-table"
  }
}

resource "aws_default_network_acl" "main" {
  default_network_acl_id = aws_vpc.main.default_network_acl_id
  subnet_ids             = [aws_subnet.vpc-public-subnet.id]

  tags = {
    Name = "${var.name_prefix}-vpc-acl"
  }

  egress {
    from_port  = 0
    to_port    = 0
    rule_no    = 100
    action     = "allow"
    protocol   = "all"
    cidr_block = "0.0.0.0/0"
  }

  ingress {
    from_port  = 123
    to_port    = 123
    rule_no    = 50
    protocol   = "udp"
    action     = "allow"
    cidr_block = "0.0.0.0/0"
  }

  ingress {
    from_port  = 80
    to_port    = 80
    rule_no    = 100
    protocol   = "tcp"
    action     = "allow"
    cidr_block = "0.0.0.0/0"
  }

  ingress {
    from_port  = 443
    to_port    = 443
    rule_no    = 101
    protocol   = "tcp"
    action     = "allow"
    cidr_block = "0.0.0.0/0"
  }

  ingress {
    from_port  = 1024
    to_port    = 65535
    rule_no    = 400
    protocol   = "tcp"
    action     = "allow"
    cidr_block = "0.0.0.0/0"
  }

  dynamic "ingress" {
    for_each = var.ip_whitelist_map
    content {
      from_port  = 22
      to_port    = 22
      rule_no    = ingress.value.rule_no
      protocol   = "tcp"
      action     = "allow"
      cidr_block = ingress.value.cidr
    }
  }
}

resource "aws_subnet" "vpc-public-subnet" {
  vpc_id            = aws_vpc.main.id
  cidr_block        = var.public_subnet_cidr
  availability_zone = "eu-west-1a"
  tags = {
    Name = "${var.name_prefix}-public-subnet"
  }
}

resource "aws_route_table" "public-subnet" {
  vpc_id = aws_vpc.main.id
  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.main.id
  }
  tags = {
    Name = "${var.name_prefix}-public-subnet-route-table"
  }
}

resource "aws_route_table_association" "public-subnet" {
  subnet_id      = aws_subnet.vpc-public-subnet.id
  route_table_id = aws_route_table.public-subnet.id
}

resource "aws_subnet" "vpc-unused-subnet" {
  vpc_id            = aws_vpc.main.id
  cidr_block        = var.unused_subnet_cidr
  availability_zone = "eu-west-1b"
  tags = {
    Name = "${var.name_prefix}-unused-subnet"
  }
}

resource "aws_network_acl" "unused-subnet-acl" {
  vpc_id     = aws_vpc.main.id
  subnet_ids = [aws_subnet.vpc-unused-subnet.id]
  tags = {
    Name = "${var.name_prefix}-unused-acl"
  }
}
