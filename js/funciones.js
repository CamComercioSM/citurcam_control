function formatearHoraCorta(fechaHoraStr) {
    if (!fechaHoraStr) return "";
    const [fecha, hora] = fechaHoraStr.split(" ");
    const [hh, mm] = hora.split(":");
    let h = parseInt(hh, 10);
    const sufijo = h >= 12 ? "p.m." : "a.m.";
    if (h === 0) h = 12;
    else if (h > 12) h = h - 12;
    return `${String(h).padStart(2, "0")}:${mm} ${sufijo}`;
}

function formatearFechaHoraLarga(fechaHoraStr) {
    if (!fechaHoraStr) return "";
    const [fecha, hora] = fechaHoraStr.split(" ");
    const [yyyy, mm, dd] = fecha.split("-");
    return `${dd}/${mm}/${yyyy} ${formatearHoraCorta(fechaHoraStr)}`;
}