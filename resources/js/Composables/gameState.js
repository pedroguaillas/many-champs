// Mapa de estilos por estado de partido: creado -> planificado -> jugando -> finalizado
const STATES = {
    creado: {
        label: 'Creado',
        badge: 'bg-gray-200 dark:bg-slate-700/50 text-gray-500 dark:text-slate-400 border border-gray-200 dark:border-white/[0.06]',
        border: 'border-l-gray-300 dark:border-l-slate-600',
    },
    planificado: {
        label: 'Planificado',
        badge: 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
        border: 'border-l-amber-400',
    },
    jugando: {
        label: 'Jugando',
        badge: 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
        border: 'border-l-blue-400',
    },
    finalizado: {
        label: 'Finalizado',
        badge: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
        border: 'border-l-emerald-400',
    },
};

const DEFAULT_STATE = STATES.creado;

export function gameState(state) {
    return STATES[state] ?? DEFAULT_STATE;
}
