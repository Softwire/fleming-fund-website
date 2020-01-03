resource "aws_db_instance" "db" {
  allocated_storage = 20
  storage_type      = "gp2"
  engine            = "mysql"
  engine_version    = "5.7.26"
  instance_class    = "db.t2.micro"
  # qq name                 = "${var.name_prefix}-db"
  username = "flemingmaster"
  # qq password             = var.DB_PASSWORD
  parameter_group_name  = "default.mysql5.7"
  multi_az              = false
  publicly_accessible   = false
  copy_tags_to_snapshot = true

  vpc_security_group_ids = [module.shared.db_sg_id]

  tags = {
    workload-type = "other"
  }
}
