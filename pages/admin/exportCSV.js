export function downloadCSV(data, headers, filename) {
    let csv = headers.join(";") + "\n";
    data.forEach(row => {
        let line = headers.map(h => `"${(row[h] ?? '').toString().replace(/"/g, '""')}"`).join(";");
        csv += line + "\n";
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename;
    a.click();
}
