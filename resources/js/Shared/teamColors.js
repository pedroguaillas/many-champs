// Colores de camiseta de los clubes para la cancha y las fichas.
// Un club sin color usa el de por defecto de su lado (local blanco, visita azul).

export const DEFAULT_COLORS = { 1: '#ffffff', 2: '#3b82f6' }

// Colores de camiseta comunes, para elegir rápido en el formulario del club
export const JERSEY_COLORS = [
    { hex: '#ffffff', name: 'Blanco' },
    { hex: '#0f172a', name: 'Negro' },
    { hex: '#dc2626', name: 'Rojo' },
    { hex: '#7f1d1d', name: 'Granate' },
    { hex: '#ea580c', name: 'Naranja' },
    { hex: '#facc15', name: 'Amarillo' },
    { hex: '#16a34a', name: 'Verde' },
    { hex: '#0ea5e9', name: 'Celeste' },
    { hex: '#2563eb', name: 'Azul' },
    { hex: '#1e3a8a', name: 'Azul marino' },
    { hex: '#7c3aed', name: 'Morado' },
    { hex: '#db2777', name: 'Rosado' },
    { hex: '#6b7280', name: 'Gris' },
]

const PITCH = '#047857'

export const isHex = (value) => typeof value === 'string' && /^#[0-9a-f]{6}$/i.test(value)

const rgb = (hex) => [1, 3, 5].map(i => parseInt(hex.slice(i, i + 2), 16))

// Luminancia relativa (WCAG)
const luminance = (hex) => {
    const [r, g, b] = rgb(hex).map(v => {
        const c = v / 255
        return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4
    })
    return 0.2126 * r + 0.7152 * g + 0.0722 * b
}

// Distancia perceptual aproximada ("redmean"), 0 = iguales
const distance = (a, b) => {
    const [r1, g1, b1] = rgb(a)
    const [r2, g2, b2] = rgb(b)
    const rm = (r1 + r2) / 2
    return Math.sqrt((2 + rm / 256) * (r1 - r2) ** 2 + 4 * (g1 - g2) ** 2 + (2 + (255 - rm) / 256) * (b1 - b2) ** 2)
}

// Texto legible sobre el color de la camiseta
export const textOn = (hex) => luminance(hex) > 0.4 ? '#0f172a' : '#ffffff'

const kit = (bg, alternate = false) => ({
    bg,
    fg: textOn(bg),
    // Camisetas verdes o muy oscuras se separan del césped con aro blanco
    ring: distance(bg, PITCH) < 200 || luminance(bg) < 0.03 ? 'rgba(255,255,255,0.9)' : 'rgba(2,6,23,0.3)',
    alternate,
})

// Colores de ambos lados. Si se parecen demasiado, la visita usa camiseta alterna
export const teamColors = (color1, color2) => {
    const home = isHex(color1) ? color1.toLowerCase() : DEFAULT_COLORS[1]
    let away = isHex(color2) ? color2.toLowerCase() : DEFAULT_COLORS[2]
    let alternate = false
    if (distance(home, away) < 150) {
        away = luminance(home) > 0.4 ? '#0f172a' : '#ffffff'
        alternate = true
    }
    return { 1: kit(home), 2: kit(away, alternate) }
}
