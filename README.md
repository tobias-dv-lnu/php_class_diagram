php_class_diagram
=================

Visualize the dependencies between php classes


Example 

http://localhost:88/php_class_diagram/src/_classDiagram.php?basepath=/var/www/quiz/src&selected=model\AttemptInformation


results in:

http://yuml.me/diagram/plain;dir:LR;scale:80;/class/%5Bmodel-AttemptInformation%7Bbg%3Agreen%7D%5D-%3E%5Bmodel-Input%7Bbg%3Agreen%7D%5D,%5Bmodel-AttemptInformation%7Bbg%3Agreen%7D%5D-%3E%5Bmodel-UniqueID%7Bbg%3Agreen%7D%5D,%5Bmodel-Input%7Bbg%3Agreen%7D%5D-%3E%5Bmodel-Input%7Bbg%3Agreen%7D%5D,%5Bmodel-InputSaver%7Bbg%3Agreen%7D%5D-%3E%5Bmodel-UniqueID%7Bbg%3Agreen%7D%5D,%5Bmodel-InputSaver%7Bbg%3Agreen%7D%5D-%3E%5Bmodel-Input%7Bbg%3Agreen%7D%5D,%5Bmodel-InputSaver%7Bbg%3Agreen%7D%5D-%3E%5Bmodel-AttemptInformation%7Bbg%3Agreen%7D%5D,%5Bmodel-InputSaver%7Bbg%3Agreen%7D%5D-%3E%5Bmodel-Input%7Bbg%3Agreen%7D%5D,%5Bmodel-QuizAttempt%7Bbg%3Agreen%7D%5D-%3E%5Bmodel-AttemptInformation%7Bbg%3Agreen%7D%5D,%5Bview-RunQuiz%7Bbg%3Aorange%7D%5D-%3E%5Bmodel-QuizAttempt%7Bbg%3Agreen%7D%5D,%5Bview-RunQuiz%7Bbg%3Aorange%7D%5D-%3E%5Bmodel-AttemptInformation%7Bbg%3Agreen%7D%5D
