const marked = require('marked');

const config = {
  context: {
    actions: [
      {
        href: '#link-to-printable-version',
        label: 'Print page'
      },
      {
        href: '#link-to-pdf',
        label: 'Download PDF'
      }
    ],
    sections: [
      {
        content: 'Psychosis is a mental health problem affecting about one in a hundred people in the UK. It causes people to perceive or interpret things differently from those around them, and it can look and feel very different from person to person. With a high relapse rate, early intervention is crucial to avoid symptoms getting worse. In addition to the human cost, unplanned admissions into hospital for psychosis are costly for the NHS.',
        title: 'Why was this work needed?'
      },
      {
        content: 'The app was developed by researchers at the Farr Institute, University of Manchester. ClinTouch  acts as an updateable,  real-time record of symptoms for someone suffering from psychosis. Throughout their day, users are asked a series of questions, about their symptoms and respond using a sliding scale on their smartphone. This allows users to record their mental health effectively and makes it easier for health care providers to identify any potentially concerning changes in patient behaviour.',
        title: 'What happened?'
      },
      {
        content: `By allowing people with psychosis to track their symptoms, ClinTouch can help people to manage their conditions better. With patient consent, their clinical team can also be alerted when symptoms worsen and intervene as early as possible. ClinTouch can also be used to inform the content of clinical consultations and thus improve the quality of doctor-patient interactions.

Compared to standard care, ClinTouch leads to significantly faster improvement in psychotic symptoms in early psychosis. Earlier intervention in psychosis could also save money for the NHS.`,
        title: 'What were the benefits?'
      },
      {
        action: {
          href: '#link-to-destination',
          icon: 'arrow',
          label: 'Clintouch website'
        },
        title: 'Where can I go for more information?'
      }
    ]
  },
  preview: '@preview-boxed'
}

// Convert markdown to HTML and add IDs
config.context.sections.forEach((section) => {
  if (section.content) {
    section.content = marked(section.content)
  }

  section.id = 'content-' + section.title.toLowerCase().replace(/\W+/g, '-').replace(/^-|-$/g, '')
})

module.exports = config
