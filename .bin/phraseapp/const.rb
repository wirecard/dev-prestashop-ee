module Const
  GITHUB_PHRASEAPP_PR_TITLE = '[PhraseApp] Update locales'.freeze
  GITHUB_PHRASEAPP_PR_BODY = 'Update locales from PhraseApp'.freeze
  GIT_PHRASEAPP_COMMIT_MSG = '[skip ci] Update translations from PhraseApp'.freeze
  GIT_PHRASEAPP_BRANCH_BASE = 'master'.freeze
  PHRASEAPP_PROJECT_ID = '706d0efd532da61719799481c0a7002c'.freeze
  PHRASEAPP_FALLBACK_LOCALE = 'en_US'.freeze

  # project-specific
  PHRASEAPP_TAG = 'prestashop'.freeze
  LOCALE_SPECIFIC_MAP = {
    'en_US': 'en',
    'de_DE': 'de',
    'ja_JP': 'ja',
  }.freeze

  # paths relative to project root
  PLUGIN_DIR = File.join('wirecardpaymentgateway', 'translations').freeze
  PLUGIN_I18N_DIR = File.join(PLUGIN_DIR, 'languages').freeze
end
