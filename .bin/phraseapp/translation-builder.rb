require 'digest'
require 'json'
require 'logger'
require_relative 'const.rb'

# build translation files
class TranslationBuilder
  def self.build(iso_lang)
    log = Logger.new(STDOUT, level: Env::DEBUG ? 'DEBUG' : 'INFO')

    if !iso_lang
      log.error("Error: TranslationBuilder.build(iso_lang) - Missing argument iso_lang")
    end

    plugin_path = Const::PLUGIN_DIR
    generated_translations_path = Const::PLUGIN_I18N_DIR
    phraseapp_translations_path = Const::PLUGIN_I18N_DIR

    translations_file_path = File.join(phraseapp_translations_path, "#{iso_lang}.json")
    translations_file = File.open(translations_file_path, 'r')
    translations = JSON.parse(translations_file.read)
    translations_file.close

    translation_file_path = File.join(generated_translations_path, "#{iso_lang}.php")
    translation_file = File.open(translation_file_path, 'w')
    add_file_header(translation_file)

    get_needed_php_files(plugin_path).each do |file_path|
      file_name_start_index = file_path.rindex('/') + 1
      file_name_end_index = file_path.rindex('.') - 1
      file_name = file_path[file_name_start_index..file_name_end_index]

      get_keys_for_php_file(file_path).each do |key|
        translation_file.puts(
          get_translation_entry(file_name, key[0], generate_translation_entry(translations, key[0], log))
        )
      end
    end

    get_needed_tpl_files(plugin_path).each do |file_path|
      file_name_start_index = file_path.rindex('/') + 1
      file_name_end_index = file_path.rindex('.') - 1
      file_name = file_path[file_name_start_index..file_name_end_index]

      get_keys_for_tpl_file(file_path).each do |key|
        translation_file.puts(
          get_translation_entry(file_name, key[0], generate_translation_entry(translations, key[0], log))
        )
      end
    end

    translation_file.close

    log.info("Built translation file #{translation_file_path}")
  end

  def self.get_all_keys()
    plugin_path = Const::PLUGIN_DIR
    keys = []

    get_needed_php_files(plugin_path).each do |file_path|
      get_keys_for_php_file(file_path).each do |key|
        keys.push(key)
      end
    end

    get_needed_tpl_files(plugin_path).each do |file_path|
      get_keys_for_tpl_file(file_path).each do |key|
        keys.push(key)
      end
    end

    keys.uniq
  end

  def self.get_keys_for_php_file(file_path)
    file = File.open(file_path, 'r')
    translation_keys = file.read.scan(/->l\(\'(.*)\'\)/).uniq
    file.close

    translation_keys
  end

  def self.get_keys_for_tpl_file(file_path)
    file = File.open(file_path, 'r')
    translation_keys = file.read.scan(/\{l s=\'(.*)\' mod/).uniq
    file.close

    translation_keys
  end

  def self.get_needed_php_files(parent_folder)
    files = []

    Dir.glob(parent_folder + '/**/*.php') do |file|
      if !file.include?('/vendor') && !file.include?('/translation')
        files.push(file)
      end
    end

    files
  end

  def self.get_needed_tpl_files(parent_folder)
    files = []

    Dir.glob(parent_folder + '/**/*.tpl') do |file|
      if !file.include? '/vendor'
        files.push(file)
      end
    end

    files
  end

  def self.get_translated_keys(file_path)
    file = File.open(file_path, 'r')
    translation_keys = file.read.scan(/  "(.*)": /)
    file.close

    translation_keys
  end

  def self.generate_translation_entry(translations, translation_key, log)
    translation_string = translations["#{translation_key}"]

    if !translation_string
      log.error("Error: Missing translation for key: #{translation_key}")
      return translation_key
    end

    translation_string.strip
  end

  def self.get_translation_entry(file_name, translation_key, translation_string)
    entry = "$_MODULE['<{"
    entry += "wirecardpaymentgateway"
    entry += "}prestashop>"
    entry += file_name.downcase
    entry += "_"
    entry += Digest::MD5.hexdigest translation_key
    entry += "'] = '"
    entry += translation_string
    entry += "';"

    entry
  end

  def self.add_file_header(translation_file)
    translation_file.puts("<?php")
    translation_file.puts("")
    translation_file.puts("global $_MODULE;")
    translation_file.puts("$_MODULE = array();")
  end
end
