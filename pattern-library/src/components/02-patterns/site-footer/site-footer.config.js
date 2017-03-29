const config = {
  context: {
    socials: [
      {
        classes: 'site-footer__social-icon',
        circular: true,
        href: '#link-to-twitter',
        icon: 'twitter',
        title: 'Follow us on Twitter'
      },
      {
        classes: 'site-footer__social-icon',
        circular: true,
        href: '#link-to-linkedin',
        icon: 'linkedin',
        title: 'Follow us on LinkedIn'
      },
      {
        classes: 'site-footer__social-icon',
        circular: true,
        href: '#link-to-facebook',
        icon: 'facebook',
        title: 'Follow us on Facebook'
      }
    ]
  },
  variants: [
    {
      context: {
        home: true
      },
      name: 'Homepage'
    }
  ]
}

module.exports = config
