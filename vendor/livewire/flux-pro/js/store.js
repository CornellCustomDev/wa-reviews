import { inject } from "./utils"

let selectorDarkMode = isUsingSelectorForDarkModeInTailwind()

if (selectorDarkMode) {
    // Make scrollbars dark in dark mode...
    inject(({ css }) => css`:root.dark { color-scheme: dark; }`)
}

document.addEventListener('alpine:init', () => {
    let flux = Alpine.reactive({
        toast(...params) {
            let detail = { slots: {}, dataset: {} }

            if (typeof params[0] === 'string') {
                detail.slots.text = params.shift()
            }

            if (typeof params[0] === 'string') {
                detail.slots.heading = detail.slots.text
                detail.slots.text = params.shift()
            }

            let options = params.shift() || {}


            if (options.text) detail.slots.text = options.text
            if (options.heading) detail.slots.heading = options.heading
            if (options.variant) detail.dataset.variant = options.variant
            if (options.position) detail.dataset.position = options.position
            // Check for undefined because 0 is a valid duration...
            if (options.duration !== undefined) detail.duration = options.duration

            document.dispatchEvent(new CustomEvent('toast-show', { detail }))
        },

        modal(name) {
            return {
                show() {
                    document.dispatchEvent(new CustomEvent('modal-show', { detail: { name } }))
                },

                close() {
                    document.dispatchEvent(new CustomEvent('modal-close', { detail: { name } }))
                },
            }
        },

        modals() {
            return { close() {
                document.dispatchEvent(new CustomEvent('modal-close', { detail: {} }))
            }}
        },

        appearance: window.localStorage.getItem('flux.appearance') || 'system',

        systemAppearanceChanged: 1, // A counter to trigger reactivity when the system appearance changes...

        get dark() {
            JSON.stringify(flux.systemAppearanceChanged) // Force reactivity on the system appearance changing...

            if (flux.appearance === 'system') {
                let media = window.matchMedia('(prefers-color-scheme: dark)')

                return media.matches
            } else {
                return flux.appearance === 'dark'
            }
        },

        set dark(value) {
            let current = this.dark

            if (value === current) return

            if (value) {
                flux.appearance = 'dark'
            } else {
                flux.appearance = 'light'
            }
        },
    })

    window.Flux = flux

    Alpine.magic('flux', () => flux)

    Alpine.effect(() => {
        applyAppearance(flux.appearance)
    })

    document.addEventListener('livewire:navigated', () => {
        applyAppearance(flux.appearance)
    })

    let media = window.matchMedia('(prefers-color-scheme: dark)')

    media.addEventListener('change', () => {
        flux.systemAppearanceChanged++

        applyAppearance(flux.appearance)
    })
})

function applyAppearance(appearance) {
    // If Tailwind dark mode is not set to selector, then remove the class and
    // local storage to ensure that any dark mode styles are not applied...
    if (! selectorDarkMode) {
        document.documentElement.classList.remove('dark')

        window.localStorage.removeItem('flux.appearance')

        return
    }

    let applyDark = () => document.documentElement.classList.add('dark')
    let applyLight = () => document.documentElement.classList.remove('dark')

    if (appearance === 'system') {
        let media = window.matchMedia('(prefers-color-scheme: dark)')

        window.localStorage.removeItem('flux.appearance')

        media.matches ? applyDark() : applyLight()
    } else if (appearance === 'dark') {
        window.localStorage.setItem('flux.appearance', 'dark')

        applyDark()
    } else if (appearance === 'light') {
        window.localStorage.setItem('flux.appearance', 'light')

        applyLight()
    }
}

function isUsingSelectorForDarkModeInTailwind() {
    // Create beacon element...
    let beacon = document.createElement('div')

    // Add attribute so that the selector will be applied...
    beacon.setAttribute('data-flux-dark-mode-beacon', '')

    document.body.appendChild(beacon)

    let beforeDarkClass = getComputedStyle(beacon).display === 'none'

    // Add the actual selector so that "&" will work...
    beacon.classList.add('dark:[&[data-flux-dark-mode-beacon]]:hidden')

    // Add .dark to detect if the selector was applied
    beacon.classList.add('dark')

    let afterDarkClass = getComputedStyle(beacon).display === 'none'

    let result = (! beforeDarkClass && afterDarkClass)

    beacon.remove()

    return result
}