module Const
  GITHUB_PHRASEAPP_PR_TITLE = '[PhraseApp] Update locales'.freeze
  GITHUB_PHRASEAPP_PR_BODY = 'Update locales from PhraseApp'.freeze
  GIT_PHRASEAPP_COMMIT_MSG = '[skip ci] Update translations from PhraseApp'.freeze
  GIT_PHRASEAPP_BRANCH_BASE = 'master'.freeze
  PHRASEAPP_PROJECT_ID = '9036e89959d471e0c2543431713b7ba1'.freeze
  PHRASEAPP_FALLBACK_LOCALE = 'en_US'.freeze

  # project-specific mappings for locales to filenames
  PHRASEAPP_TAG = 'prestashop'.freeze
  LOCALE_SPECIFIC_MAP = {
    'en_US': 'en',
    'de_DE': 'de',
    'id_ID': 'id',
    'ja_JP': 'ja',
    'ko_KR': 'ko',
    'pl_PL': 'pl',
    'zh_TW': 'tw',
    'zh_CN': 'zh'
  }.freeze

  # paths relative to project root
  PLUGIN_DIR = 'wirecardpaymentgateway'.freeze
  PLUGIN_I18N_DIR = File.join(PLUGIN_DIR, 'translations').freeze
end
