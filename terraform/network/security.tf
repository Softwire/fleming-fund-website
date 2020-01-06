resource "aws_security_group" "webserver" {
  name        = "${var.name_prefix}-sg-webserver"
  description = "${var.name_prefix} Webserver security group" # "Fleming Fund stage webserver security group"
  vpc_id      = aws_vpc.main.id

  tags = {
    Name = "${var.name_prefix}-secgrp-webserver"
  }

  lifecycle {
    create_before_destroy = true
  }

  ingress {
    description = "NTP"
    from_port   = 123
    to_port     = 123
    protocol    = "udp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    description = "HTTP"
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    description = "HTTPS"
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    description = "Ephemeral ports"
    from_port   = 1024
    to_port     = 65535
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    description = "SSH"
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = [
      for el in var.ip_whitelist_map :
      el.cidr
    ]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

resource "aws_security_group" "efs" {
  name        = "${var.name_prefix}-secgrp-efs"
  description = "Fleming Fund Security group for wp-uploads filesystem"
  vpc_id      = aws_vpc.main.id

  tags = {
    Name = "${var.name_prefix}-secgrp-efs"
  }

  ingress {
    description     = "Webserver NFS access"
    from_port       = 2049
    to_port         = 2049
    protocol        = "tcp"
    security_groups = [aws_security_group.webserver.id]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

resource "aws_security_group" "db" {
  name        = "${var.name_prefix}-secgrp-db"
  description = "Fleming fund Database security group"
  vpc_id      = aws_vpc.main.id

  tags = {
    Name = "${var.name_prefix}-secgrp-db"
  }

  ingress {
    description     = "Webserver access"
    from_port       = 3306
    to_port         = 3306
    protocol        = "tcp"
    security_groups = [aws_security_group.webserver.id]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}
