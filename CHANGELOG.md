# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [v1.0.0-alpha] - 2017-12-01
### Added
- Request and Response injection into SmartController constructor
- Route parameters injected by SmartStrategy in SmartController instances
- SmartController::url() builds a URL based on route name and parameters
- SmartStrategy allows lazy return by controllers
