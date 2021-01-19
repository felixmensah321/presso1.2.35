const messages = {
  en: {
    meta: {
      login: 'Please login to order',
      loading: 'Processing your order',
      hello: 'Hello',
      step1: 'Log in',
      step2: 'Delivery address',
      step3: 'Products selection',
      step4: 'Payment with TWINT'
    },
    steps: {
      identification: 'Log in',
      cart: 'Products selection',
      payment: 'Payment with TWINT',
      delivery: 'Delivery address',
      preview: 'Cart review'
    },
    login: {
      zip: 'Zip Code',
      cmid: 'Client Number',
      message: 'Sorry, we could’t recognize you – would you like to log in?',
      btn: 'Continue',
      hello: 'Hello',
      autofill: 'Please enter your zip code to continue',
      rememberDevice: 'Remeber this device ?',
      error: 'Login Error'
    },
    logout: 'Logout',
    delivery: {
      name: 'Next-day delivery',
      price: 'Free',
      optIn: 'J’accepte de recevoir des communications à propos des nouveaux produits, services et offres privilèges par e-mail de la part de Nespresso.',
      createNewAccount: 'Je souhaite créer un compte client Nespresso et bénéficier des avantages liés (voir les <a href="http://google.fr">termes et conditions</a>).'
    },
    address: {
      conditions: 'Place your order before 7pm, from Monday to Thursday, and we’ll deliver it the following day.',
      message: 'Please confirm your delivery address',
      gender: 'Title',
      email: 'Email address',
      lastName: 'Last name',
      firstName: 'First name',
      address: 'Address',
      zip: 'Postal code',
      contactName: 'Contact Name',
      company: 'Company',
      city: 'City',
      continue: 'Continue',
      guestContinue: 'Create account and continue'
    },
    gender: {
      empty: '',
      male: 'Mister',
      female: 'Miss',
      unspecified: 'Not specified'
    },
    guest: {
      message: 'Don’t have a client number?',
      btn: 'Continue as a guest'
    },
    product: {
      hello: 'Hello',
      message: 'Your selection',
      btn: 'Place my order',
      total: 'Total',
      vat: 'VAT included',
      listLabel: 'Browse'
    },
    loading: {
      title: 'You will be automatically redirected to the TWINT application to make the payment.',
      message: 'After validation of your payment, you will receive an order confirmation email.'
    },
    order: {
      thank: 'Thank you',
      message: 'Your order is confirmed, you will receive a confirmation email in a few minutes.',
      btn: 'Return to TWINT',
      details: 'My Order Details',
      payed: 'Total paid with Twint on ',
      status: 'Order Status'
    },
    cart: {
      close: 'Close',
      title: 'YOUR SELECTION',
      total: 'Total',
      empty: 'Your basket is empty'
    },
    errorManagement: {
      genericTitle: 'An error has happened',
      genericMessage: 'Error message',
      accountExist: 'Account Already Exist',
      sessionError: {
        title: 'Your session has expired',
        message1: 'No payment was made.',
        message2: 'Please scan the QR code and try again.',
        link: ''
      },
      deviceError: {
        title: 'This website is only accessible by scanning the dedicated QR code with your TWINT mobile application.',
        message1: 'Find out all details and order the QR code sticker on:',
        message2: '',
        link: 'https://www.nespresso.com/ch/en/twint'
      }
    },
    notfound: {
      title: 'Oops...',
      message: 'Unfortunately, something didn\'t go as planned.',
      line2: 'No payment was made.',
      line3: 'Please try again and scan the QR code.'
    },
    errorPayment: {
      noPayment: 'Payment has been cancelled',
      scanAgain: 'Please try again and scan the QR code.'
    },
    popIn: {
      chooseQty: 'Choose a quantity',
      errorText: 'You can purchase this product only by multiples of {multi}',
      errorReplace: 'We have modified the quantity to {multi}'
    }
  },
  fr: {
    meta: {
      login: 'Veuillez vous connecter pour commander',
      loading: 'Traitement de votre commande en cours',
      hello: 'Bonjour',
      step1: 'Connection',
      step2: 'Adresse de livraison',
      step3: 'Sélection des produits',
      step4: 'Paiement avec TWINT'
    },
    steps: {
      identification: 'Connection',
      cart: 'Sélection des produits',
      payment: 'Paiement avec TWINT',
      delivery: 'Adresse de livraison',
      preview: 'Cart review'
    },
    login: {
      zip: 'Code postal',
      cmid: 'Numéro de client',
      message: 'Nous n’avons pas pu vous identifier, souhaitez-vous vous connecter ?',
      btn: 'Continuez',
      hello: 'Bonjour',
      autofill: 'Veuillez saisir votre code postal pour continuer',
      rememberDevice: 'Remeber this device ?',
      error: 'Login Error'
    },
    address: {
      conditions: 'Passez commande avant 19h du lundi au jeudi, nous vous livrons le lendemain.',
      message: 'Veuillez confirmer votre adresse de livraison',
      gender: 'Titre',
      email: 'E-mail',
      lastName: 'Nom',
      firstName: 'Prénom',
      address: 'Adresse',
      zip: 'Code postal',
      city: 'Ville',
      contactName: 'Contact Name',
      company: 'Company',
      continue: 'Continuez',
      guestContinue: 'Create account and continue'
    },
    logout: 'Déconnexion',
    delivery: {
      name: 'Livraison le lendemain',
      price: 'Gratuit',
      optIn: 'J’accepte de recevoir des communications à propos des nouveaux produits, services et offres privilèges par e-mail de la part de Nespresso.',
      createNewAccount: 'Je souhaite créer un compte client Nespresso et bénéficier des avantages liés (voir les <a href="http://google.fr">termes et conditions</a>).'
    },
    gender: {
      empty: '',
      male: 'Monsieur',
      female: 'Madame',
      unspecified: 'Non '
    },
    guest: {
      message: 'Vous n’avez pas de numéro de client ?',
      btn: 'Continuez en tant qu’invité(e)'
    },
    product: {
      hello: 'Bonjour',
      message: 'Votre sélection',
      btn: 'Validez votre commande',
      total: 'Total',
      vat: 'TVA incluse',
      listLabel: 'Parcourez'
    },
    loading: {
      title: 'Vous allez être redirigé automatiquement vers l’application TWINT pour effectuer le paiement…',
      message: 'Après validation de votre paiement, vous recevrez un email de confirmation de commande.'
    },
    order: {
      thank: 'Merci',
      message: 'Votre commande est confirmée, vous allez recevoir un email de confirmation dans quelques minutes.',
      btn: 'Retournez sur TWINT',
      details: 'Détail de ma commande',
      payed: 'Total payé avec TWINT le ',
      status: 'Order Status'
    },
    cart: {
      close: 'Fermer',
      title: 'VOTRE SÉLECTION',
      total: 'Total',
      empty: 'Votre panier est vide'
    },
    errorManagement: {
      genericTitle: 'An error has happened',
      genericMessage: 'Error message',
      accountExist: 'Account Already Exist',
      sessionError: {
        title: 'Votre session a expiré',
        message1: 'Aucun paiement n’a été effectué.',
        message2: 'Veuillez recommencer en scannant à nouveau le code QR.',
        link: ''
      },
      deviceError: {
        title: 'Ce site est accessible uniquement en scannant le code QR dédié avec votre application mobile TWINT',
        message1: 'Découvrez tous les détails et commandez le code QR autocollant sur :',
        message2: '',
        link: 'https://www.nespresso.com/ch/fr/twint'
      }
    },
    notfound: {
      title: 'Oups...',
      message: 'Malheureusement quelque chose ne s’est pas passé comme prévu.',
      line2: 'Aucun paiement n’a été effectué.',
      line3: 'Veuillez recommencer et scanner le QR code.'
    },
    errorPayment: {
      noPayment: 'Le paiement a été annulé',
      scanAgain: 'Veuillez recommencer et scanner le QR code.'
    },
    popIn: {
      chooseQty: 'Choisir une quantité',
      errorText: 'Vous ne pouvez acheter ce produit que par multiples de {multi}',
      errorReplace: 'Nous avons remplacé la quantité par {multi}'
    }
  },
  de: {
    meta: {
      login: 'Zum Bestellen bitte anmelden',
      loading: 'Ihre Bestellung wird bearbeitet',
      hello: 'Hallo',
      step1: 'Anmelden',
      step2: 'Lieferadresse',
      step3: 'Auswahl der Produkte',
      step4: 'Zahlung mit TWINT'
    },
    steps: {
      identification: 'Anmelden',
      cart: 'Auswahl der Produkte',
      payment: 'Zahlung mit TWINT',
      delivery: 'Lieferadresse',
      preview: 'Cart review'
    },
    login: {
      zip: 'Postleitzahl',
      cmid: 'Kundennummer',
      message: 'Wir haben Sie leider nicht erkannt, möchten Sie sich anmelden?',
      btn: 'Weiter',
      hello: 'Hallo',
      autofill: 'Geben Sie bitte Ihre Postleitzahl ein, um fortzufahren',
      rememberDevice: 'Remeber this device ?',
      error: 'Login Error'
    },
    address: {
      conditions: 'Bestellen Sie von Montag bis Donnerstag vor 19 Uhr und erhalten Sie Ihre Bestellung am folgenden Tag.',
      message: 'Bitte bestätigen Sie Ihre Lieferadresse',
      gender: 'Titel',
      email: 'E-Mail Adresse',
      lastName: 'Name',
      firstName: 'Vorname',
      address: 'Adresszeile',
      zip: 'Postleitzahl',
      city: 'Ortschaft',
      contactName: 'Contact Name',
      company: 'Company',
      continue: 'Weiter',
      guestContinue: 'Create account and continue'
    },
    logout: 'Abmelden',
    delivery: {
      name: 'Lieferung am folgenden Tag',
      price: 'Kostenlos',
      optIn: 'J’accepte de recevoir des communications à propos des nouveaux produits, services et offres privilèges par e-mail de la part de Nespresso.',
      createNewAccount: 'Je souhaite créer un compte client Nespresso et bénéficier des avantages liés (voir les <a href="http://google.fr">termes et conditions</a>).'
    },
    gender: {
      empty: '',
      male: 'Herr',
      female: 'Frau',
      unspecified: 'Unbestimmt'
    },
    guest: {
      message: 'Sie haben keine Kundennummer?',
      btn: 'Als Gast fortfahren'
    },
    product: {
      hello: 'Hallo',
      message: 'Ihre Auswahl',
      btn: 'BESTELLUNG BESTÄTIGEN',
      total: 'Gesamtbetrag',
      vat: 'MWST inbegriffen',
      listLabel: 'Bestellen Sie'
    },
    loading: {
      title: 'Sie werden automatisch zur TWINT Applikation weitergeleitet, um die Zahlung vorzunehmen.',
      message: 'Nach Bestätigung Ihrer Zahlung erhalten Sie eine Auftragsbestätigung per E-Mail.'
    },
    order: {
      thank: 'Danke',
      message: 'Ihre Bestellung ist bestätigt, Sie erhalten in wenigen Minuten eine Bestätigungs-E-Mail.',
      btn: 'Zurück zu TWINT',
      details: 'Meine Bestelldaten',
      payed: 'Total bezahlt mit TWINT am ',
      status: 'Order Status'
    },
    cart: {
      close: 'Schliessen',
      title: 'IHRE AUSWAHL',
      total: 'Gesamtbetrag',
      empty: 'Ihr Warenkorb ist leer'
    },
    errorManagement: {
      genericTitle: 'An error has happened',
      genericMessage: 'Error message',
      accountExist: 'Account Already Exist',
      sessionError: {
        title: 'Ihre Session ist abgelaufen',
        message1: 'Es wurde keine Zahlung getätigt.',
        message2: 'Bitte versuchen Sie es erneut, indem Sie den QR- Code scannen.',
        link: ''
      },
      deviceError: {
        title: 'Diese Website ist nur zugänglich, wenn Sie den speziellen QR-Code mit Ihrer TWINT-Mobilanwendung scannen.',
        message1: 'Finden Sie alle Details heraus und bestellen Sie den QR-Code Aufkleber unter:',
        message2: '',
        link: 'https://www.nespresso.com/ch/de/twint'
      }
    },
    notfound: {
      title: 'Hoppla...',
      message: 'Leider ist etwas schief gelaufen.',
      line2: 'Es wurde keine Zahlung getätigt.',
      line3: 'Bitte versuchen Sie es erneut, indem Sie den QR-Code scannen.'
    },
    errorPayment: {
      noPayment: 'Die Zahlung wurde storniert',
      scanAgain: 'Bitte versuchen Sie es erneut, indem Sie den QR-Code scannen.'
    },
    popIn: {
      chooseQty: 'Eine Menge wählen',
      errorText: 'Dieses Produkt ist nur im Multipack von {multi} erhältlich',
      errorReplace: 'Wir haben die Menge auf {multi} angepasst'
    }
  },
  it: {
    meta: {
      login: 'Effettua il login per ordinare',
      loading: 'Stiamo elaborando il tuo ordine',
      hello: 'Salve',
      step1: 'Login',
      step2: 'Indirizzo di consegna',
      step3: 'Selezione dei prodotti',
      step4: 'Pagamento con TWINT'
    },
    steps: {
      identification: 'Login',
      cart: 'Selezione dei prodotti',
      payment: 'Pagamento con TWINT',
      delivery: 'Indirizzo di consegna',
      preview: 'Cart review'
    },
    login: {
      zip: 'Codice postale',
      cmid: 'ID del cliente Nespresso ',
      message: 'Non siamo riusciti a riconoscerti, vuoi effettuare l’accesso?',
      btn: 'Continua',
      hello: 'Salve',
      autofill: 'Inserisci il tuo codice postale per continuare',
      rememberDevice: 'Remeber this device ?',
      error: 'Login Error'
    },
    address: {
      conditions: 'Ordina entro le 19 dal lunedì al giovedì, consegneremo l’indomani.',
      message: 'Si prega di confermare l\'indirizzo di consegna',
      gender: 'Titolo',
      email: 'Indirizzo E-mail',
      lastName: 'Nome',
      firstName: 'Cognome',
      address: 'Riga indirizzo',
      zip: 'Codice postale',
      city: 'Città',
      contactName: 'Contact Name',
      company: 'Company',
      continue: 'Continua',
      guestContinue: 'Create account and continue'
    },
    logout: 'Logout',
    delivery: {
      name: 'Consegna l\'indomani',
      price: 'Gratuito',
      optIn: 'J’accepte de recevoir des communications à propos des nouveaux produits, services et offres privilèges par e-mail de la part de Nespresso.',
      createNewAccount: 'Je souhaite créer un compte client Nespresso et bénéficier des avantages liés (voir les <a href="http://google.fr">termes et conditions</a>).'
    },
    gender: {
      empty: '',
      male: 'Signore',
      female: 'Signora',
      unspecified: 'Imprecisato'
    },
    guest: {
      message: 'Non hai un conto cliente Nespresso?',
      btn: 'Continua come ospite'
    },
    product: {
      hello: 'Salve',
      message: 'La tua selezione',
      btn: 'CONFERMA IL MIO ORDINE',
      total: 'Totale',
      vat: 'IVA inclusa',
      listLabel: 'Ordina'
    },
    loading: {
      title: 'Verrete automaticamente reindirizzati all\'applicazione TWINT per effettuare il pagamento.',
      message: 'Dopo la convalida del pagamento, riceverete un\'e- mail di conferma dell\'ordine.'
    },
    order: {
      thank: 'Grazie',
      message: 'Il vostro ordine è confermato, riceverete una e-mail di conferma in pochi minuti.',
      btn: 'Torna su TWINT',
      details: 'Dettaglio del mio ordine',
      payed: 'Totale pagato con TWINT il ',
      status: 'Order Status'
    },
    cart: {
      close: 'Chiudere',
      title: 'LA SUA SELEZIONE',
      total: 'Totale',
      empty: 'Il Suo carrello è vuoto'
    },
    errorManagement: {
      genericTitle: 'An error has happened',
      genericMessage: 'Error message',
      accountExist: 'Account Already Exist',
      sessionError: {
        title: 'La sua sessione è scaduta',
        message1: 'Non è stato effettuato alcun pagamento.',
        message2: 'Riprovare e scansionare il codice QR.',
        link: ''
      },
      deviceError: {
        title: 'Questo sito è accessibile solo attraverso la scansione del codice QR dedicato con la vostra applicazione mobile TWINT.',
        message1: 'Scopri tutti i dettagli e ordina il adesivo com codice QR su:',
        message2: '',
        link: 'https://www.nespresso.com/ch/it/twint'
      }
    },
    notfound: {
      title: 'Ops!',
      message: 'Purtroppo qualcosa non è andato come previsto.',
      line2: 'Non è stato effettuato alcun pagamento.',
      line3: 'Riprovare e scansionare il codice QR.'
    },
    errorPayment: {
      noPayment: 'Il pagamento è stato annullato',
      scanAgain: 'Riprovare e scansionare il codice QR.'
    },
    popIn: {
      chooseQty: 'Selezionare una quantità',
      errorText: 'Può acquistare questo prodotto solo con multipli di {multi}',
      errorReplace: 'Abbiamo modificato la quantità di {multi}'
    }
  }
}

export default messages
