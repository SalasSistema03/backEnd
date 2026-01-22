function calcularTotal(index) {
    const extraordinaria = parseFloat(document.querySelector(`input[name="importe_extraordinaria_${index}"]`)
        .value) || 0;
    const ordinaria = parseFloat(document.querySelector(`input[name="importe_ordinaria_${index}"]`).value) || 0;
    const total = extraordinaria + ordinaria;

    document.querySelector(`input[name="total_${index}"]`).value = total.toFixed(2);
}