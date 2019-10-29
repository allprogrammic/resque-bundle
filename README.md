# AllProgrammic Resque Bundle

# Tâches récurrentes
## Mise en place

Vous pouvez définir des tâches récurrentes en définissant un fichier de configuration propre aux tâches comme suit :

```bash
tasks_name:
    cron: "*/5 * * * *" (cron format)
    class: ~
    queue: ~
    args: ~
    description: ~
```

## Alertes

N'oubliez pas d'inclure et configurer le bundle SwiftmailerBundle pour pouvoir utiliser les alertes

```bash
public function registerBundles()
{
    $bundles = array(
        // ...
        new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
    );

    // ...
}
```

## Exécution

Pour mettre en place vos tâches récurrentes, il vous suffit de précisier le fichier à utiliser lors de l'appel à la
commande `resque:recurring` comme suit :

```bash
resque:recurring [le_chemin_de_votre_fichier_de_configuration]
```

## Expressions du cron

```bash
*    *    *    *    *
-    -    -    -    -
|    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
|    |    |    +---------- month (1 - 12)
|    |    +--------------- day of month (1 - 31)
|    +-------------------- hour (0 - 23)
+------------------------- min (0 - 59)
```