/* GSAP ANIMATION */
const tl = gsap.timeline({})

/* animate from in the center */
tl.fromTo(
    '.login__content',
    {
        y:-800,
        scaleX: .2,
        scaleY: .5,
        opacity: 0
    },
    {
  y:0,
  scaleX: .2,
  scaleY: .5,
  opacity: 1,
  duration: 1.5,
  ease: 'power3.out'
    }
)

/* expand verticalilly */
tl.to(
    '.login__content',
    {
        scaleY: 1,
        duration: .6,
        ease: 'power3.out'
    }, '-=0.3'
)

/* expand horizontally */
tl.to(
    '.login__content',
    {
        scaleX: 1,
        duration: .7,
        ease: 'power3.out'
    }, '-=0.2'
)

/* animated  background image */
tl.to(
    '.login__img',
    {
        scale: 1.08,
        duration: 5,
ease: 'power1.inOut',
repeat: -1,
yoyo: true,
transformOrigin: 'center center'
    }
)

/* animate from */
gsap.defaults({opacity: 0, y: -60, ease: 'power2.out', duration: 1.2})
gsap.from('.login__title', {delay: 2.5})
gsap.from('.login__form > *', {delay: 2.7, stagger: .2})
gsap.from('.login__img', {y: 0, x: 100, delay: 3.2, ease: 'elastic.out(1,0.6)'})
