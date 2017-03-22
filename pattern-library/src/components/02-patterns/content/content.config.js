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
  default: 'Case study',
  preview: '@preview-boxed',
  variants: [
    {
      context: {},
      name: 'Key information'
    },
    {
      context: {},
      name: 'Accordion only'
    }
  ]
}

// Add dummy accordion content to variants
config.variants.forEach((variant) => {
  variant.context.accordions = [
    {
      content: '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ullamcorper porta eleifend. Curabitur condimentum neque vitae porta fermentum. Suspendisse consequat turpis leo, sed elementum velit fermentum at. Quisque libero lorem, consectetur id blandit tempor, cursus elementum lectus. Nullam maximus fringilla metus eget eleifend. Donec augue sapien, lacinia eget dui sed, imperdiet rhoncus nulla. Aenean vel convallis urna, id porta purus. Vestibulum semper ante eget neque gravida, et facilisis metus semper. Nunc in metus ut nibh lacinia varius in quis est. Suspendisse quis tincidunt erat. Phasellus est arcu, cursus sit amet elementum in, lacinia vel leo.</p>',
      id: 'patient-data',
      level: 3,
      title: 'Patient data'
    },
    {
      content: '<p>Etiam mattis, leo et ultricies tincidunt, nisi elit lobortis risus, nec tristique augue justo ac massa. Donec venenatis, nibh ac elementum auctor, sapien nisi pretium nulla, non hendrerit libero justo eget lorem. Donec sagittis blandit libero quis convallis. Vestibulum sagittis interdum augue, bibendum mattis magna elementum vitae. Donec sapien libero, maximus id volutpat fermentum, faucibus eu ante. Fusce interdum maximus eleifend. Curabitur id nisi id sem efficitur interdum. Sed in sollicitudin enim. Vivamus commodo massa a diam faucibus porta. Vestibulum condimentum, magna vitae venenatis porttitor, urna arcu condimentum sem, at luctus orci mauris tristique nibh.</p>',
      id: 'individual-care',
      level: 3,
      title: 'Individual care'
    },
    {
      content: '<p>Cras ac vestibulum lorem. Quisque lobortis magna ac posuere suscipit. Sed blandit vel lorem nec facilisis. Nulla eget varius tellus, quis consectetur velit. Ut dui urna, iaculis quis massa in, dapibus vulputate tellus. Sed dictum neque et convallis imperdiet. Quisque id sem ultricies, ultrices elit sit amet, rhoncus eros. Fusce mollis rhoncus sem sit amet pulvinar.</p>',
      id: 'improving-health',
      level: 3,
      title: 'Improving health, care and services through research and planning'
    }
  ]
})

// Convert markdown to HTML and add IDs
config.context.sections.forEach((section) => {
  if (section.content) {
    section.content = marked(section.content)
  }

  section.id = 'content-' + section.title.toLowerCase().replace(/\W+/g, '-').replace(/^-|-$/g, '')
})

module.exports = config
