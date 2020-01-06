locals {
  bucket_name     = "${var.name_prefix}-static-site-errors"
  error_html_file = "../src/fallback-site/index.html"
}

resource "aws_s3_bucket" "static-site-errors" {
  bucket = local.bucket_name
  acl    = "private"

  website {
    index_document = "index.html"
    error_document = "index.html"
  }

  tags = {
    Name = local.bucket_name
  }

  versioning {
    enabled = false
  }
}

resource "aws_s3_bucket_policy" "static-site-errors" {
  bucket = aws_s3_bucket.static-site-errors.id

  policy = <<POLICY
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "PublicReadGetObject",
      "Effect": "Allow",
      "Principal": "*",
      "Action": "s3:GetObject",
      "Resource": "arn:aws:s3:::${aws_s3_bucket.static-site-errors.id}/*"
    }
  ]
}
POLICY
}

resource "aws_s3_bucket_object" "error-page" {
  for_each = toset(formatlist("%d", local.static_site_error_codes))
  bucket   = aws_s3_bucket.static-site-errors.id
  key      = "error/${each.value}.html"
  source   = local.error_html_file
  etag     = filemd5(local.error_html_file)
  content_type = "text/html"
}

resource "aws_s3_bucket_object" "logo-png" {
  bucket = aws_s3_bucket.static-site-errors.id
  key    = "error/fleming_logo.png"
  source = "../src/fallback-site/fleming_logo.png"
  etag   = filemd5("../src/fallback-site/fleming_logo.png")
  content_type = "image/png"
}

resource "aws_s3_bucket_object" "logo-svg" {
  bucket = aws_s3_bucket.static-site-errors.id
  key    = "error/fleming_logo.svg"
  source = "../src/fallback-site/fleming_logo.svg"
  etag   = filemd5("../src/fallback-site/fleming_logo.svg")
  content_type = "image/svg+xml"
}

resource "aws_s3_bucket_object" "error-index" {
  bucket = aws_s3_bucket.static-site-errors.id
  key    = "index.html"
  source = local.error_html_file
  etag   = filemd5(local.error_html_file)
  content_type = "text/html"
}
