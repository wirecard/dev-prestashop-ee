require 'logger'
require 'open3'
require 'rainbow/refinement'
require 'simple_po_parser'
require_relative 'const.rb'
require_relative 'env.rb'
require_relative 'wd-git.rb'
require_relative 'wd-github.rb'

using Rainbow

# Project-specific helpers
class WdProject
  # attr_reader :pot_path
  # attr_reader :pot_new_path

  def initialize
    @log = Logger.new(STDOUT, level: Env::DEBUG ? 'DEBUG' : 'INFO')
    # @pot_path = File.join(Const::PLUGIN_I18N_DIR, Const::LOCALE_FILE_PREFIX + '.pot')
    # @pot_new_path = @pot_path + '.new'
    @repo = Env::TRAVIS_REPO_SLUG
    @head = Env::TRAVIS_BRANCH
  end

  # Returns true if source code has modified keys compared to the existing POT.
  def worktree_has_key_changes?
    pot_generate && has_key_changes?
  end

  # Generates a new POT for the plugin source files, using the WP-CLI.
  def pot_generate
    # @log.info('curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar')
    # stdout, stderr, status = Open3.capture3('curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar')
    # @log.debug(stdout)
    # if status != 0
    #   @log.error(stderr)
    #   exit(1)
    # end

    # @log.info("php wp-cli.phar i18n make-pot #{Const::PLUGIN_DIR} #{@pot_new_path}")
    # stdout, stderr, status = Open3.capture3("php wp-cli.phar i18n make-pot #{Const::PLUGIN_DIR} #{@pot_new_path}")
    # @log.info(stdout)
    # if status != 0
    #   @log.error(stderr)
    #   exit(1)
    # end

    true
  end

  # Parses a PO/POT file and returns an array of messages.
  # Used instead of SimplePoParser.parse(path), due to the latter not properly closing the file on Windows.
  def parse_file(path)
    # file = File.open(path, 'r')
    # if file.gets =~ /\r$/
    #   # detected Windows line ending
    #   file.close
    #   file = File.open(path, 'rt')
    # else
    #   file.rewind
    # end
    # messages = []
    # file.each_line("\n\n") do |block|
    #   block.strip! # don't parse empty blocks
    #   messages << parse_block(block) if block != ''
    # end
    # file.close
    # messages
  end

  # Parses a message block using SimplePoParser.
  def parse_block(block)
    SimplePoParser.parse_message(block)
  end

  # Compares two POT files and returns true if they have any difference in keys, false otherwise.
  def has_key_changes?
  #   pot = parse_file(@pot_path)
  #   existing_keys = pot.map { |h| h[:msgid] }.select { |k| !k.empty? }.uniq
  #   existing_keys += pot.map { |h| h[:msgid_plural] }.select { |k| !k.nil? }.uniq

  #   pot_new = parse_file(@pot_new_path)
  #   new_keys = pot_new.map { |h| h[:msgid] }.select { |k| !k.empty? }.uniq
  #   new_keys += pot_new.map { |h| h[:msgid_plural] }.select { |k| !k.nil? }.uniq

  #   @log.info("Number of keys in the existing POT: #{existing_keys.length}")
  #   @log.info("Number of keys in the new POT: #{new_keys.length}")

  #   @log.info("Removed keys: #{existing_keys - new_keys}")
  #   @log.info("Added keys: #{new_keys - existing_keys}")

  #   # keys are unique; we use the intersection to detect differences
  #   has_key_changes = (new_keys.length != existing_keys.length) || (new_keys & existing_keys != new_keys)
  #   if has_key_changes
  #     @log.warn('Changes to translatable keys have been detected in the working tree.'.yellow.bright)
  #   else
  #     @log.info('No changes to translatable keys have been detected in the working tree.'.green.bright)
  #   end

    # has_key_changes
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
