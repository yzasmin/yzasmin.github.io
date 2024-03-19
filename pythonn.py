import csv
import os
import math
import json
from collections import defaultdict


def compute_tf(text):
    tf_dict = defaultdict(float)
    words = text.split()
    total_words = len(words)

    for word in words:
        tf_dict[word] += 1 / total_words

    return tf_dict

def compute_idf(documents):
    idf_dict = defaultdict(float)
    total_documents = len(documents)

    for document in documents:
        seen_words = set()
        for word in document.split():
            if word not in seen_words:
                idf_dict[word] += 1
                seen_words.add(word)

    for word, count in idf_dict.items():
        idf_dict[word] = math.log(total_documents / float(count))

    return idf_dict

def compute_score_map(texts, labels, idf):
    score_map = defaultdict(float)

    for text, label in zip(texts, labels):
        tf = compute_tf(text)
        for word, tf_val in tf.items():
            tf_idf = tf_val * idf[word]
            if label == "hate":
                score_map[word] -= tf_idf
            else:
                score_map[word] += tf_idf

    return score_map

def evaluate_message(message, score_map):
    score = 0
    tf = compute_tf(message)
    for word in message.split():
        score += tf.get(word, 0) * score_map.get(word, 0)
    return score

labels = {}
with open('annotations_metadata.csv', newline='', encoding='utf-8') as csvfile:
    reader = csv.DictReader(csvfile)
    for row in reader:
        labels[row['file_id']] = row['label']


texts = []

sampled_train_path = 'sampled_train\\sampled_train'

for file_id in labels:
    file_path = os.path.join(sampled_train_path, file_id + '.txt')
    try:
        with open(file_path, 'r', encoding='utf-8') as file:
            texts.append(file.read())
    except FileNotFoundError:
        print(f"Le fichier {file_id}.txt n'a pas été trouvé.")


# Calculer IDF pour tout le corpus
idf = compute_idf(texts)

score_map = compute_score_map(texts, labels, idf)

# Écrire score_map dans un fichier JSON
with open('score_map.json', 'w') as f:
    json.dump(score_map, f)

# Utilisez cette fonction pour évaluer de nouveaux messages
message = "Votre message à évaluer"
message_score = evaluate_message(message, score_map)
if message_score > 0:
    print("Message valide")
else:
    print("Message potentiellement haineux")

