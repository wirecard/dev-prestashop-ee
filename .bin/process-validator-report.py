import os
from bs4 import BeautifulSoup

REPORT_FILE = os.environ["REPORT_FILE"]


def process_validation_report():
    print("---Processing validator results---")
    with open(REPORT_FILE, 'r') as report_file:
        report = report_file.read()

    error_flag = False
    soup = BeautifulSoup(report, features="html.parser")
    steps = soup.find_all('div', {'class': 'tab-pane validation-step'})
    for blocks in steps:
        block_name = blocks.get('id')
        validation_nok = blocks.find('div', {'class': 'validation-nok'})
        if validation_nok:
            section_title = validation_nok.find_all('dt', {'class': 'section-title'})
            if section_title:
                error_messages = validation_nok.find_all('blockquote', {'class': 'error-message'})
                results_with_name = dict(zip(error_messages, section_title))
                for error_message in error_messages:
                    for error in error_message.find_all('li'):
                        if not 'vendor' in error.text:
                            print "Error in module {}: " \
                                  "{} : {}".format(block_name,
                                                   results_with_name[error_message].text,
                                                   error.text)
                            error_flag = True
    if error_flag:
        print "Validator test failed"
        exit(1)


def main():
    process_validation_report()


if __name__ == "__main__":
    main()
