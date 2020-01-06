resource "aws_cloudfront_distribution" "cf" {
  enabled         = true
  is_ipv6_enabled = true

  aliases = var.cloudfront_domain_aliases

  origin {
    domain_name = var.origin_domain
    origin_id   = "${var.name_prefix}-custom-origin"

    custom_origin_config {
      http_port                = 80
      https_port               = 443
      origin_keepalive_timeout = 5
      origin_protocol_policy   = "https-only"
      origin_read_timeout      = 30
      origin_ssl_protocols = [
        "TLSv1.1",
        "TLSv1.2",
      ]
    }
  }

  origin {
    domain_name = "fleming-fund-static-site-errors.s3.amazonaws.com"
    origin_id   = "S3-fleming-fund-static-site-errors"
  }

  default_cache_behavior {
    allowed_methods  = ["GET", "HEAD", "OPTIONS"]
    cached_methods   = ["GET", "HEAD", "OPTIONS"]
    compress         = true
    target_origin_id = "${var.name_prefix}-custom-origin"

    forwarded_values {
      headers      = ["Host"]
      query_string = true

      cookies {
        forward = "whitelist"
        whitelisted_names = [
          "low-bandwidth",
          "wordpress_logged_in_*"
        ]
      }
    }

    viewer_protocol_policy = "allow-all"
  }

  ordered_cache_behavior {
    allowed_methods        = ["GET", "HEAD", "OPTIONS"]
    cached_methods         = ["GET", "HEAD", "OPTIONS"]
    compress               = true
    path_pattern           = "error/*"
    smooth_streaming       = false
    target_origin_id       = "S3-fleming-fund-static-site-errors"
    trusted_signers        = []
    viewer_protocol_policy = "allow-all"

    forwarded_values {
      headers                 = []
      query_string            = false
      query_string_cache_keys = []

      cookies {
        forward           = "none"
        whitelisted_names = []
      }
    }
  }

  ordered_cache_behavior {
    allowed_methods = [
      "DELETE",
      "GET",
      "HEAD",
      "OPTIONS",
      "PATCH",
      "POST",
      "PUT",
    ]
    cached_methods = [
      "GET",
      "HEAD",
      "OPTIONS",
    ]
    compress               = true
    path_pattern           = "wp-admin*"
    smooth_streaming       = false
    target_origin_id       = "${var.name_prefix}-custom-origin"
    trusted_signers        = []
    viewer_protocol_policy = "redirect-to-https"

    forwarded_values {
      headers = [
        "*",
      ]
      query_string            = true
      query_string_cache_keys = []

      cookies {
        forward           = "all"
        whitelisted_names = []
      }
    }
  }

  ordered_cache_behavior {
    allowed_methods = [
      "DELETE",
      "GET",
      "HEAD",
      "OPTIONS",
      "PATCH",
      "POST",
      "PUT",
    ]
    cached_methods = [
      "GET",
      "HEAD",
      "OPTIONS",
    ]
    compress               = true
    path_pattern           = "/wp-login.php*"
    smooth_streaming       = false
    target_origin_id       = "${var.name_prefix}-custom-origin"
    trusted_signers        = []
    viewer_protocol_policy = "redirect-to-https"

    forwarded_values {
      headers = [
        "*",
      ]
      query_string            = true
      query_string_cache_keys = []

      cookies {
        forward           = "all"
        whitelisted_names = []
      }
    }
  }

  custom_error_response {
    error_caching_min_ttl = 300
    error_code            = 500
    response_code         = 500
    response_page_path    = "/error/500.html"
  }
  custom_error_response {
    error_caching_min_ttl = 300
    error_code            = 501
    response_code         = 501
    response_page_path    = "/error/501.html"
  }
  custom_error_response {
    error_caching_min_ttl = 300
    error_code            = 502
    response_code         = 502
    response_page_path    = "/error/502.html"
  }
  custom_error_response {
    error_caching_min_ttl = 300
    error_code            = 503
    response_code         = 503
    response_page_path    = "/error/503.html"
  }
  custom_error_response {
    error_caching_min_ttl = 300
    error_code            = 504
    response_code         = 504
    response_page_path    = "/error/504.html"
  }

  tags = {
  }

  restrictions {
    geo_restriction {
      restriction_type = "none"
    }
  }

  price_class = var.is_production ? "PriceClass_All" : "PriceClass_100"

  viewer_certificate {
    acm_certificate_arn            = var.acm_certificate_arn
    cloudfront_default_certificate = false
    minimum_protocol_version       = "TLSv1.1_2016"
    ssl_support_method             = "sni-only"
  }

  wait_for_deployment = false # CloudFront deployments tak ~10 minutes
}
