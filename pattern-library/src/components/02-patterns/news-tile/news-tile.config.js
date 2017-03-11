const formatDate = (date) => {
  const months = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec'
  ]

  return {
    datetime: date.toISOString(),
    human: date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear()
  }
}

const config = {
  context: {
    cta: {
      icon: 'arrow',
      label: 'Read more',
      small: true
    },
    date: formatDate(new Date('27 February 2017 12:34 UTC')),
    image: '/images/placeholder/news-tile-documents.jpg',
    level: '2',
    title: 'NHS misplaced half a million patient documents'
  },
  preview: '@preview-boxed'
}

module.exports = config
