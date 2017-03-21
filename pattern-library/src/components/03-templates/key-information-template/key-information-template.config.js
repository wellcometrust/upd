const config = {
  context: {
    content: [
      {
        name: 'intro',
        context: {
          content: '<p>The current vocabulary for the use of patient data in care, treatment and research is difficult, complex and confusing.  Many different words are used to describe the same thing, and many of those words are unnecessarily technical (for example pseudonymised, key-coded, de-identified for limited disclosure).  This acts as a significant barrier to discussing the use of data with the public in ways that can build both understanding and confidence.</p>\n\n<p>We think that an important part of improving conversations about patient data is getting the words right, in a way that is accurate but also accessible and meaningful. If everyone is on the same page in using these words, it’ll be easier for clinicians, patients, researchers and the public to have informed discussions about how patient data is used and why.</p>'
        }
      },
      {
        name: 'content--key-information'
      }
    ],
    header: {
      title: 'Key information'
    },
    sidebar: [
      {
        name: 'table-of-contents',
        context: {
          items: [
            {
              active: true,
              id: 'content-how-to-guide',
              title: '‘How to’ guide'
            },
            {
              id: 'content-identifiability',
              title: 'Identifiability'
            },
            {
              id: 'content-dos-and-donts',
              title: 'DOs and DON’Ts'
            },
            {
              id: 'content-the-research',
              title: 'The research'
            }
          ]
        }
      }
    ]
  }
}

module.exports = config
