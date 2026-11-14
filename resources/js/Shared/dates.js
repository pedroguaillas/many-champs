// Fechas de partidos a partir del texto que llega del servidor ("2026-11-15", "19:00:00"),
// sin pasar por zona horaria.

const parts = (date) => String(date ?? '').slice(0, 10).split('-').map(Number)

// [año, mes, día] del dispositivo. Usar en onMounted: el reloj del servidor puede no coincidir
export const todayParts = () => {
    const now = new Date()
    return [now.getFullYear(), now.getMonth() + 1, now.getDate()]
}

const format = (date, options) => {
    const [y, m, d] = parts(date)
    if (!y) return ''
    return new Intl.DateTimeFormat('es', options).format(new Date(y, m - 1, d)).replace(/\./g, '')
}

// "sáb 15 nov"; con `today` (de todayParts) dice "Hoy" o "Mañana"
export const dayLabel = (date, today = null) => {
    const [y, m, d] = parts(date)
    if (!y) return ''
    if (today) {
        const diff = Math.round((Date.UTC(y, m - 1, d) - Date.UTC(today[0], today[1] - 1, today[2])) / 86400000)
        if (diff === 0) return 'Hoy'
        if (diff === 1) return 'Mañana'
        if (diff === -1) return 'Ayer'
    }
    return format(date, { weekday: 'short', day: 'numeric', month: 'short' })
}

// "sábado, 15 de noviembre"
export const longDay = (date) => format(date, { weekday: 'long', day: 'numeric', month: 'long' })

// "19:00"
export const hour = (time) => String(time ?? '').match(/(\d{2}:\d{2})(:\d{2})?$/)?.[1] ?? ''
