Le modèle `runwayml/stable-diffusion-v1-5` n'existe plus sur Hugging Face depuis 2024 : utiliser le miroir `stable-diffusion-v1-5/stable-diffusion-v1-5`.

# Stable Diffusion v1.5 dans un ancien notebook

- Runway a supprimé son organisation Hugging Face (voir huggingface/diffusers#9322) ; `from_pretrained('runwayml/stable-diffusion-v1-5')`
  échoue.
- Miroir officiel communautaire : `stable-diffusion-v1-5/stable-diffusion-v1-5`.
- Projet 1 : l'identifiant d'origine est laissé dans le notebook 06 pour rester fidèle, le README indique le remplacement.
