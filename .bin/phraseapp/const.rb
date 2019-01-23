# frozen_string_literal: true

module Const
  GITHUB_PHRASEAPP_PR_TITLE = '[PhraseApp] Update locales'
  GITHUB_PHRASEAPP_PR_BODY = 'Update locales from PhraseApp'
  GIT_PHRASEAPP_COMMIT_MSG = '[skip ci] Update translations from PhraseApp'
  GIT_PHRASEAPP_BRANCH_BASE = 'master'
  PHRASEAPP_PROJECT_ID = '9036e89959d471e0c2543431713b7ba1'
  PHRASEAPP_FALLBACK_LOCALE = 'en_US'

  # project-specific mappings for locales to filenames
  PHRASEAPP_TAG = 'prestashop'
  LOCALE_SPECIFIC_MAP = {
    'en_US': 'en',
    'de_DE': 'de',
    'id_ID': 'id',
    'ja_JP': 'ja',
    'ko_KR': 'ko',
    'pl_PL': 'pl',
    'zh_TW': 'tw',
    'zh_CN': 'zh',
  }.freeze

  # paths relative to project root
  PLUGIN_DIR = 'wirecardpaymentgateway'
  PLUGIN_I18N_DIR = File.join(PLUGIN_DIR, 'translations')
end
