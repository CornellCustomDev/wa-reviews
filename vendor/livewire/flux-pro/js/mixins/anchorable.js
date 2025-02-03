import { computePosition, autoUpdate, flip, shift, offset, size } from '@floating-ui/dom'
import { Mixin } from './mixin.js'

export class Anchorable extends Mixin {
    boot({ options }) {
        options({
            reference: null,
            auto: true,
            position: 'bottom start',
            gap: '5',
            offset: '0',
            matchWidth: false,
            crossAxis: false,
        })

        if (this.options().reference === null) return
        if (this.options().position === null) return

        let [ setPosition, cleanupDurablePositioning ] = createDurablePositionSetter(this.el)

        let reposition = anchor(this.el, this.options().reference, setPosition, {
            position: this.options().position,
            gap: this.options().gap,
            offset: this.options().offset,
            matchWidth: this.options().matchWidth,
            crossAxis: this.options().crossAxis,
        })

        let cleanupAutoUpdate = () => {}

        this.reposition = (...args) => {
            if (this.options().auto) {
                cleanupAutoUpdate = autoUpdate(this.options().reference, this.el, reposition)
            } else {
                // We need to pass `null` as the first argument, as it is the `event` parameter that isn't used here...
                reposition(null, ...args)
            }
        }

        this.cleanup = () => {
            cleanupAutoUpdate()
            cleanupDurablePositioning()
        }
    }
}

function anchor(target, invoke, setPosition, { position, offset: offsetValue, gap, matchWidth, crossAxis }) {
    // We need to accept `event` here, even though it's not used, as it is a parameter supplied by the `autoUpdate` function...
    return (event, forceX, forceY) => {
        computePosition(invoke, target, {
            placement: compilePlacement(position), // Placements: ['top', 'top-start', 'top-end', 'right', 'right-start', 'right-end', 'bottom', 'bottom-start', 'bottom-end', 'left', 'left-start', 'left-end']
            middleware: [
                // Offset needs to be first, as per the Floating UI docs...
                offset({
                    mainAxis: Number(gap),
                    alignmentAxis: Number(offsetValue),
                }),
                flip(),
                shift({ padding: 5, crossAxis: crossAxis }),
                size({
                    padding: 5,
                    apply({rects, elements, availableHeight}) {
                        if (matchWidth) {
                            Object.assign(elements.floating.style, {
                                width: `${rects.reference.width}px`,
                            });
                        }

                        // Limit the height of the dropdown to the available space...
                        elements.floating.style.maxHeight = availableHeight >= elements.floating.scrollHeight ? '' : `${availableHeight}px`;
                    }
                }),
            ],
        }).then(({ x, y }) => {
            setPosition(forceX || x, forceY || y)
        })
    }
}

function compilePlacement(anchor) {
    return anchor.split(' ').join('-')
}

// Libraries like morphdom will whipe out the anchor positioning styles after a morph.
// This makes those styles "durable" and prevents them frmo being removed...
function createDurablePositionSetter(target) {
    let position = (x, y) => {
        Object.assign(target.style, {
            position: 'absolute',
            overflowY: 'auto',
            left: `${x}px`,
            top: `${y}px`,
            // This is required to reset the `popover` default styles, otherwise the dropdown appears in the middle of the screen...
            right: 'auto',
            bottom: 'auto',
        })
    }

    let lastX, lastY

    let observer = new MutationObserver(() => position(lastX, lastY))

    return [
        (x, y) => { // Set position...
            lastX = x
            lastY = y

            observer.disconnect()

            position(lastX, lastY)

            observer.observe(target, { attributeFilter: ['style'] })
        },
        () => { // Cleanup...
            observer.disconnect()
        }
    ]
}
