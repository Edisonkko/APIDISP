<?php

class chatbot_Ctrl {

    import nltk
    from nltk.stem.lancaster import LancasterStemmer
    stemmer = LancasterStemmer()
    import numpy
    import tflearn
    import tensorflow.compat.v1 as tf
    import json
    import random
    import pickle
    import requests
    from datetime import datetime
    from flask import Flask, request, jsonify

    app = Flask(__name__)

    # Cargar los datos de entrenamiento y configuración del chatbot
    with open("contenido.json", encoding='utf-8') as archivo:
        datos = json.load(archivo)

    # Preprocesamiento y entrenamiento del modelo
    palabras = []
    tags = []
    auxX = []
    auxY = []

    for contenido in datos["contenido"]:
        for patrones in contenido["patrones"]:
            auxPalabra = nltk.word_tokenize(patrones)
            palabras.extend(auxPalabra)
            auxX.append(auxPalabra)
            auxY.append(contenido["tag"])

            if contenido["tag"] not in tags:
                tags.append(contenido["tag"])

    palabras = [stemmer.stem(w.lower()) for w in palabras if w != "?"]
    palabras = sorted(list(set(palabras)))
    tags = sorted(tags)

    entrenamiento = []
    salida = []

    salidaVacia = [0 for _ in range(len(tags))]

    for x, documento in enumerate(auxX):
        cubeta = []
        auxPalabra = [stemmer.stem(w.lower()) for w in documento]
        for w in palabras:
            if w in auxPalabra:
                cubeta.append(1)
            else:
                cubeta.append(0)
        filaSalida = salidaVacia[:]
        filaSalida[tags.index(auxY[x])] = 1
        entrenamiento.append(cubeta)
        salida.append(filaSalida)

    entrenamiento = numpy.array(entrenamiento)
    salida = numpy.array(salida)

    # Crear y entrenar el modelo
    tf.compat.v1.reset_default_graph()
    red = tflearn.input_data(shape=[None, len(entrenamiento[0])])
    red = tflearn.fully_connected(red, 19)
    red = tflearn.fully_connected(red, 19)
    red = tflearn.fully_connected(red, len(salida[0]), activation="softmax")
    red = tflearn.regression(red)
    modelo = tflearn.DNN(red)
    modelo.fit(entrenamiento, salida, n_epoch=1000, batch_size=10, show_metric=True)
    modelo.save("modelo.tflearn")

    @app.route('/chat', methods=['POST'])
    def chat():
        data = request.json
        entrada = data['entrada']
        respuesta = mainBot(entrada)
        return jsonify({'respuesta': respuesta})

    def mainBot(entrada):
        cubeta = [0 for _ in range(len(palabras))]
        entradaProcesada = nltk.word_tokenize(entrada)
        entradaProcesada = [stemmer.stem(palabra.lower()) for palabra in entradaProcesada]
        for palabraIndividual in entradaProcesada:
            for i, palabra in enumerate(palabras):
                if palabra == palabraIndividual:
                    cubeta[i] = 1
        resultados = modelo.predict([numpy.array(cubeta)])
        resultadosIndices = numpy.argmax(resultados)
        tag = tags[resultadosIndices]
        respuesta_mostrada = False  # Bandera para controlar si se ha mostrado la respuesta del bot
        for tagAux in datos["contenido"]:
            if tagAux["tag"] == tag:
                respuesta = tagAux["respuestas"]
                if tagAux["tag"] == "citas_medicas":
                    respuesta_bot = random.choice(respuesta)
                    respuesta_mostrada = True
                    registrar_cita(entrada)  # Llamada a la función para registrar la cita en la API
                elif tagAux["tag"] == "ver_citas":
                    respuesta_bot = random.choice(respuesta)
                    respuesta_mostrada = True
                    mostrar_citas()  # Llamada a la función para mostrar las citas del usuario
        if not respuesta_mostrada:
            respuesta_bot = random.choice(respuesta)
        return respuesta_bot

    def registrar_cita(entrada):
        # Obtener los datos necesarios para registrar la cita
        cedula = input("Ingresa tu número de cédula:")

        # Llamar a la API para buscar al paciente por número de cédula
        response_paciente = requests.get('http://localhost/APIDISPENSARIO/pacientexCed/' + cedula)
        datos_paciente = response_paciente.json()
        if datos_paciente["cantidad"] > 0:
            id_paciente = datos_paciente["info"]["items"][0]["PAC_ID"]
            print("paciente: ", id_paciente)
            # Otros campos del paciente...

            # Obtener las especialidades disponibles desde la API
            response_especialidades = requests.get('http://localhost/APIDISPENSARIO/listardoctoresespecialidad')
            if response_especialidades.status_code == 200:
                especialidades = response_especialidades.json()
                if especialidades["cantidad"] > 0:
                    print("Especialidades disponibles:")
                    for i, especialidad in enumerate(especialidades["info"]["items"]):
                        print(f"{i+1}. {especialidad['especialidad']}")

                    # Pedir al paciente que elija la especialidad
                    eleccion_especialidad = input("Elige la especialidad para la cita (ingresa el número correspondiente): ")
                    try:
                        indice_especialidad = int(eleccion_especialidad) - 1
                        if indice_especialidad >= 0 and indice_especialidad < len(especialidades["info"]["items"]):
                            especialidad_elegida = especialidades["info"]["items"][indice_especialidad]["especialidad"]
                            medico = especialidades["info"]["items"][indice_especialidad]["medico_nombre"]
                            medico_id = especialidades["info"]["items"][indice_especialidad]["MED_ID"]
                            print("Especialidad elegida:", especialidad_elegida)
                            print("Doctor:", medico)

                            # Continuar con el código para ingresar la fecha y registrar la cita
                            fecha_input = input("Ingresa la fecha de la cita (formato: DD/MM/AAAA): ")
                            # Validar el formato de fecha ingresado
                            try:
                                fecha = datetime.strptime(fecha_input, "%d/%m/%Y")
                                # La fecha se ha ingresado correctamente en el formato especificado
                                # Puedes utilizar la variable 'fecha' para almacenar y procesar la fecha de la cita
                                print("Fecha ingresada:", fecha)

                                # Obtener los horarios disponibles para la fecha y la especialidad desde la API
                                response_horarios = requests.get(f'http://localhost/APIDISPENSARIO/listarhoraxFecha/{fecha}/{medico_id}')
                                if response_horarios.status_code == 200:
                                    horarios = response_horarios.json()
                                    if horarios["cantidad"] > 0:
                                        print("Horarios disponibles:")
                                        for j, horario in enumerate(horarios["info"]["items"]):
                                            print(f"{j+1}. {horario['NOM_HORA']}")
                                        # Continuar con el código para seleccionar el horario y registrar la cita
                                        horario_elegido = input("Elige el horario para la cita (ingresa el número correspondiente): ")
                                        indice_horario = int(horario_elegido) - 1
                                        if indice_horario >= 0 and indice_horario < len(horarios["info"]["items"]):
                                            hora_elegida=horarios["info"]["items"][indice_horario]["NOM_HORA"]
                                            hora_id=horarios["info"]["items"][indice_horario]["HORA_ID"]
                                            print("Hora:", hora_elegida)
                                            fecha_str = fecha.strftime('%Y-%m-%d')
                                            # Llamar a la API para registrar la cita
                                            datosCitas = {
                                                'pac_id': id_paciente,
                                                'med_id': medico_id,
                                                'fecha': fecha_str,
                                                'hora_id': hora_id
                                            }

                                            response_cita = requests.post('http://localhost/APIDISPENSARIO/creaCita', data=datosCitas)

                                            if response_cita.status_code == 200:
                                                response_data = response_cita.json()
                                                print("xD: ", response_data["mensaje"])
                                            else:
                                                print("falló")
                                        else:
                                            print("Opción inválida. Por favor, elige un número de especialidad válido.")

                                    else:
                                        print("No hay horarios disponibles para la fecha y la especialidad seleccionadas.")
                                else:
                                    print("Error al obtener los horarios. Por favor, intenta nuevamente.")
                            except ValueError:
                                print("Formato de fecha incorrecto. Por favor, ingresa la fecha en el formato DD/MM/AAAA.")
                        else:
                            print("Opción inválida. Por favor, elige un número de especialidad válido.")
                    except ValueError:
                        print("Opción inválida. Por favor, ingresa un número válido para la especialidad.")
                else:
                    print("No hay especialidades disponibles en este momento.")
            else:
                print("Error al obtener las especialidades. Por favor, intenta nuevamente.")
        else:
            print("No se encontró al paciente con el número de cédula proporcionado.")

    def mostrar_citas():
        cedula = input("Ingresa tu número de cédula:")
        # Llamar a la API para buscar al paciente por número de cédula
        response_paciente = requests.get('http://localhost/APIDISPENSARIO/pacientexCed/' + cedula)
        datos_paciente = response_paciente.json()
        if datos_paciente["cantidad"] > 0:
            id_paciente = datos_paciente["info"]["items"][0]["PAC_ID"]
            # Llamar a la API para obtener las citas del usuario
            fecha_input = input("Ingresa la fecha de la cita (formato: DD/MM/AAAA): ")
            fecha = datetime.strptime(fecha_input, "%d/%m/%Y")
            response_citas = requests.get(f'http://localhost/APIDISPENSARIO/listarcitaxFecha/{id_paciente}/{fecha}')
            response = requests.get('http://localhost/APIDISPENSARIO/listarcitasxID/10')

            if response_citas.status_code == 200:
                data = response_citas.json()
                citas = data["info"]["items"]
                print("Citas:")
                for cita in citas:
                    print("- Nombre: " + cita["nombre_paciente"])
                    print("- Especialidad: " + cita["ESP_NOM"])
                    print("- Medico: " + cita["nombre_medico"])
                    print("- Fecha: " + cita["FECHA"])
                    print("- Hora: " + cita["NOM_HORA"])

                    # Agrega otros campos necesarios para mostrar la cita según la estructura de tu API
                    print()
            else:
                print("Error al obtener las citas. Por favor, intenta nuevamente.")

    if __name__ == '__main__':
        app.run()


}
?>
