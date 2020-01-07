resource "aws_efs_file_system" "wp-uploads" {
  tags = {
    Name = "${var.name_prefix}-filesystem-wpuploads"
    environment = var.environment_tag
  }
}

resource "aws_efs_mount_target" "wp-uploads-webserver" {
  file_system_id  = aws_efs_file_system.wp-uploads.id
  subnet_id       = var.vpc_public_subnet_id
  security_groups = [var.efs_sg_id]
}
