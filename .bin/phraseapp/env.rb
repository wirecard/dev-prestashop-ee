# frozen_string_literal: true

module Env
  DEBUG = ENV['DEBUG'] == '1'

  GITHUB_TOKEN = (ENV['GITHUB_TOKEN'] || '')
  PHRASEAPP_PULL = (ENV['PHRASEAPP_PULL'] || '')
  PHRASEAPP_TOKEN = (ENV['PHRASEAPP_TOKEN'] || '')
  TRAVIS_BRANCH = (ENV['TRAVIS_BRANCH'] || '')
  TRAVIS_REPO_SLUG = (ENV['TRAVIS_REPO_SLUG'] || '')
end
