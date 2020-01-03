resource "aws_efs_file_system" "wp-uploads" {
  tags = {
    Name = "${var.name_prefix}-filesystem-wpuploads"
  }
}

resource "aws_efs_mount_target" "wp-uploads-webserver" {
  file_system_id = aws_efs_file_system.wp-uploads.id
  subnet_id      = module.shared.vpc_public_subnet_id
  security_groups = [module.shared.efs_sg_id]
}
