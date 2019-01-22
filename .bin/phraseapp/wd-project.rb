require 'logger'
require 'rainbow/refinement'
require_relative 'const.rb'
require_relative 'env.rb'
require_relative 'wd-git.rb'
require_relative 'wd-github.rb'
require_relative 'translation-builder.rb'

using Rainbow

# Project-specific helpers
class WdProject
  attr_reader :translations_path
  attr_reader :translations_new_path

  def initialize
    @log = Logger.new(STDOUT, level: Env::DEBUG ? 'DEBUG' : 'INFO')
    @repo = Env::TRAVIS_REPO_SLUG
    @head = Env::TRAVIS_BRANCH

    @phraseapp_fallback_locale = Const::PHRASEAPP_FALLBACK_LOCALE
    @locale_specific_map = Const::LOCALE_SPECIFIC_MAP
    @translations_path = File.join(
      Const::PLUGIN_I18N_DIR,
      "#{@locale_specific_map[@phraseapp_fallback_locale.to_sym] || @phraseapp_fallback_locale}.json"
    )
    @translations_new_path = @translations_path + '.new'
  end

  # Returns true if source code has modified keys compared to the downloaded locale file of the fallback locale id
  def worktree_has_key_changes?
    json_generate && has_key_changes?
  end

  # Compares the keys from source and PhraseApp and returns true if they have any difference in keys, false otherwise.
  def has_key_changes?
    source_keys = TranslationBuilder.get_all_keys
    translated_keys = TranslationBuilder.get_translated_keys(@translations_path)

    @log.info("Number of unique keys in source: #{source_keys.length}")
    @log.info("Number of keys on PhraseApp: #{translated_keys.length}")

    has_key_changes = false
    source_keys.each do |key|
      if !translated_keys.index(key)
        @log.warn("Change to translatable key has been detected in the working tree. key: #{key[0]}".yellow.bright)
        has_key_changes = true
      end
    end

    if has_key_changes || source_keys.length != translated_keys.length
      @log.warn('Changes to translatable keys have been detected in the working tree.'.yellow.bright)
      return true
    end

    @log.info('No changes to translatable keys have been detected in the working tree.'.green.bright)
    false
  end

  # Generates a new json file with all keys and the available en translations.
  def json_generate
    @log.info('Generate new translations json file for PhraseApp upload')

    source_keys = TranslationBuilder.get_all_keys

    translations_file = File.open(translations_path, 'r')
    translations_object = JSON.parse(translations_file.read)
    translations_file.close

    key_value_object = {}

    source_keys.each do |source_key|
      if translations_object.key?(source_key[0])
        key_value_object[source_key[0]] = translations_object[source_key[0]]
      else
        @log.warn("New Key found: #{source_key[0]}".yellow.bright)
        key_value_object[source_key[0]] = ''
      end
    end

    new_file = File.open(translations_new_path, 'w')
    new_file.puts JSON.pretty_generate(key_value_object)
    new_file.close

    true
  end

  # Adds, commits, pushes to remote any modified/untracked files in the i18n dir. Then creates a PR.
  def commit_push_pr_locales()
    path = Const::PLUGIN_I18N_DIR
    base = Const::GIT_PHRASEAPP_BRANCH_BASE
    commit_msg = Const::GIT_PHRASEAPP_COMMIT_MSG
    pr_title = Const::GITHUB_PHRASEAPP_PR_TITLE
    pr_body = Const::GITHUB_PHRASEAPP_PR_BODY

    WdGit.new.commit_push(@repo, @head, path, commit_msg)
    WdGithub.new.create_pr(@repo, base, @head, pr_title, pr_body)
  end
end
