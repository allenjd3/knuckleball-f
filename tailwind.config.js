import preset from './vendor/filament/support/tailwind.config.preset'
import defaultTheme from 'tailwindcss/defaultTheme';

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    safelist: [
        'bg-green-300',
        'text-green-800',
        'bg-blue-300',
        'text-blue-800',
        'bg-red-300',
        'text-red-800',
        'bg-yellow-300',
        'text-yellow-800',
    ],
    theme: {
        extend: {
            fontFamily: {
                'display': ['industry-inc-base', ...defaultTheme.fontFamily.serif],
            },
        }
    }
}
