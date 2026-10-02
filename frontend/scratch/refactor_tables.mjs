import fs from 'fs';
import path from 'path';

const viewsPaths = [
  'd:/Project/e-ticket-sarangan/frontend/src/views/admin',
  'd:/Project/e-ticket-sarangan/frontend/src/views/petugas'
];

function processDirectory(dir) {
  const files = fs.readdirSync(dir);
  for (const file of files) {
    const filePath = path.join(dir, file);
    if (fs.statSync(filePath).isDirectory()) {
      processDirectory(filePath);
    } else if (filePath.endsWith('.vue')) {
      let content = fs.readFileSync(filePath, 'utf-8');
      let modified = false;

      // Wrap Pagination inside DataTable's #pagination slot if it's placed immediately after DataTable
      const regex = /<\/DataTable>\s*<Pagination([^>]+)\/>/g;
      
      if (regex.test(content)) {
        content = content.replace(regex, (match, p1) => {
          return `    <template #pagination>\n      <Pagination${p1}/>\n    </template>\n  </DataTable>`;
        });
        modified = true;
      }

      if (modified) {
        fs.writeFileSync(filePath, content, 'utf-8');
        console.log(`Refactored Pagination slot in ${filePath}`);
      }
    }
  }
}

viewsPaths.forEach(processDirectory);
console.log('Done refactoring Pagination slots.');
