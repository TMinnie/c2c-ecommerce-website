export function downloadCSV(data, headers, filename = "export.csv", options = {}) {
    const delimiter = options.delimiter || ";";

    const escape = (val) => `"${String(val).replace(/"/g, '""')}"`;

    const csvHeader = headers.map(escape).join(delimiter);
    const csvRows = data.map(row => {
        return headers.map(header => escape(row[header])).join(delimiter);
    });

    const csvContent = [csvHeader, ...csvRows].join("\n");

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);

    const link = document.createElement("a");
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
