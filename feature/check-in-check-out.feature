Feature: checking in and out of a building

  # Questo scenario ci serve solo a vedere la differenza tra business language e implementation
  # Vediamo di descrivere cosa abbiamo implementato fin'ora

  # Questo scenario e' molto "anemico", perche' evento ~= command
  # Questo lo scrivo io:
  Scenario: Checking in
    Given the building "Doing Roma" was registered
    When "Bob" checks into the "Doing Roma" building
    Then "Bob" should have been checked into the "Doing Roma" building

  # Scenario un po' piu' interessante - sarebbe stato ottimo per vedere l'ordine degli eventi:
  # Questo lo scrivete voi:
  Scenario: A double check-in leads to a check-in anomaly to be detected
    Given the building "Doing Roma" was registered
    And "Bob" was checked into the "Doing Roma" building
    # Notare: best practice in behat e gherkin => 1 solo "When" (azione) per scenario!
    # Un po' come una sola method call su un system under test in phpunit
    When "Bob" checks into the "Doing Roma" building
    Then "Bob" should have been checked into the "Doing Roma" building
    # Notare: "And" => stessa interazione di sopra
    # Given-And = Given Given
    # When-And = When When
    # Then-And = Then Then
    And a check-in anomaly was detected for "Bob" in "Doing Roma"

  # E anche questo lo scrivete voi
  Scenario: A check-out without a previous check-in leads to a check-in anomaly to be detected
    Given the building "Doing Roma" was registered
    When "Bob" checks out of the "Doing Roma" building
    Then "Bob" should have been checked out of the "Doing Roma" building
    And a check-in anomaly was detected for "Bob" in "Doing Roma"

  # Effettivamente questo va tradotto dalla spec (putroppo i clienti adorano comunicare
  # via docx di 70 pagine al pezzo) e poi inviato al cliente PRIMA di cominciare con l'implementazione
  # Vantaggio: ogni modifica a `.feature` => scope change => deadline change => $$$ change
  # Idealmente il cliente viene educato a scrivere sta roba - fattibilissimo se si trova qualcuno
  # che va a lavoro la mattina con l'intento di lavorare
  # Competenza non veramente necessaria => se questo lo puo' fare la persona che scriverebbe altrimenti
  # le spec, allora tutte le competenze ci sono gia'.
  # La complessita' e spiegare questo linguaggio, chiamato "Plain English"
  # Comunque l'idea e' di portare la specifica allo stesso livello per tutti. Tutti devono poterlo
  # leggere e scrivere
  # Ad occhio e' come in un processo legale: niente di implicito, tutto esplicito. Qualcuno deve
  # putroppo fare il lavoro di stare seduto al dattilografo e scrivere TUTTO.

  # Perfetto scenario per mostrare un algoritmo:
  # Esempio:
#
#  Scenario: Select the best price across the available products without destroying the market
#    Given I have following products:
#     | Name | Price |
#     | Shoe | 120   |
#     | Shoe | 100   |
#     | Shoe | 50    |
#    And an average market price for "Shoe" of 110 EUR
#    When I select a fitting price for "Shoe"
#    # Questo lo capiscono tutti:
#    Then the selected price for "Shoe" should have been 100 EUR

  # Questo non e' un "algoritmo", ma solo una descrizione di cosa il cliente vorrebbe
  # Si possono fare esempi con coordinate GPS, con immagini, con liste di file, etc

  # L'importante e' rappresentare il tutto con esempi comprensibili
#
#  Scenario: Select the best price across the available products without destroying the market
#    Given I have a set of products
#    And an average market price for a product
#    When I select a fitting price for a product
#    # Questo non significa un accidente:
#    Then a competitive price was selected for the product

  # E' un po' come diritto legale: posso leggermi il codice civile e sperare di capirci qualcosa,
  # Oppure assumere qualcuno che mi fa esempi comprensibili
  # Implementare le regole e' un dettaglio del codice - gli esempi sono un modo migliore di
  # trasmettere feature e bug

  # Ok, scriviamo sto test :-)




































