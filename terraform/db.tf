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

resource "aws_db_subnet_group" "db-subnet" {
  name       = "default-vpc-02a0c6b651aae14cf"
  subnet_ids = [module.shared.vpc_public_subnet_id, module.shared.vpc_unused_subnet_id]

  tags = {
    Name = "fleming-fund-stage-db-subnet-group"
  }
}
