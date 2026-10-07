import os
import re

directories = [
    r'd:\Project\e-ticket-sarangan\frontend\src\views\admin',
    r'd:\Project\e-ticket-sarangan\frontend\src\views\petugas'
]

for dirpath in directories:
    for root, _, files in os.walk(dirpath):
        for file in files:
            if file.endswith('.vue'):
                filepath = os.path.join(root, file)
                with open(filepath, 'r', encoding='utf-8') as f:
                    content = f.read()

                if '<Pagination' in content and '@update:perPage' not in content:
                    match = re.search(r'function handlePageChange\([^)]*\)\s*\{[^}]*(fetch[a-zA-Z0-9_]*)\(\)', content)
                    if match:
                        fetch_fn = match.group(1)
                        new_content = re.sub(
                            r'(@page-change="handlePageChange")\s*/?>',
                            rf'\g<1> @update:perPage="v => {{ perPage = v; currentPage = 1; {fetch_fn}() }}" />',
                            content
                        )
                        if new_content != content:
                            with open(filepath, 'w', encoding='utf-8') as f:
                                f.write(new_content)
                            print(f'Updated {file} with {fetch_fn}')
                    else:
                        print(f'Failed to find fetch function for {file}')
