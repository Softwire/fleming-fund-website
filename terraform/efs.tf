resource "aws_efs_file_system" "wp-uploads" {
  tags = {
    Name = "fleming-fund-stage-filesystem-wpuploads"
  }
}
