import { Component, OnInit } from '@angular/core';
import { AdminService } from '../services/admin';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { Topic } from '../models/topic';
import { Course } from '../models/course';
import { Lesson } from '../models/lesson';
import { User } from '../models/user';
import { Purchase } from '../models/purchase';

@Component({
  selector: 'app-admin',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './admin.html',
  styleUrl: './admin.scss'
})
export class Admin implements OnInit {
  showLessonsSection: boolean = false;
  showUsersSection: boolean = false;
  showPurchasesSection: boolean = false;
  openedTopicId: number | null = null;
  openedCourseId: number | null = null; 
  topics: Topic[] = [];
  users: User[] = [];
  purchases: Purchase[] = [];
  showAddForm: boolean = false;
  showAddUserForm: boolean = false;
  showAddPurchaseForm: boolean = false;
  selectedAddType: 'topic' | 'course' | 'lesson' | '' = '';
  purchaseType: 'lesson' | 'course' | '' = '';
  newTopic: Pick<Topic, 'name'> = { name: '' };
  newCourse: Pick<Course, 'title' | 'price'> & { topicId: number } = { title: '', price: 0, topicId: 0 };
  newLesson: Pick<Lesson, 'title' | 'price' | 'content' | 'videoUrl'> & { courseId: number } = { title: '', price: 0, content: '', videoUrl: '', courseId: 0 };
  newUser: { name: string; email: string; password: string; roles: string[]; isVerified: boolean } = { name: '', email: '', password: '', roles: [], isVerified: false };
  newPurchase: { customerId: number; lessonId?: number; courseId?: number } = { customerId: 0 };
  editingTopic: Topic | null = null;
  editingCourse: (Course & { topicId: number }) | null = null;
  editingLesson: (Lesson & { courseId: number }) | null = null;
  editingUser: User | null = null;
  editingPurchase: { id: number; customerId: number; lessonId: number | null; courseId: number | null; } | null = null;
  confirmDelete: { type: 'topic' | 'course' | 'lesson' | 'user' | 'purchase', id: number, name: string } | null = null;
  errorMessage: string = '';
  successMessage: string = '';

  constructor(private adminService: AdminService) {}

  private showSuccess(message: string): void {
    this.successMessage = message;
    setTimeout(() => (this.successMessage = ''), 5000);
  }

  private showError(message: string): void {
    this.errorMessage = message;
    setTimeout(() => (this.errorMessage = ''), 5000);
  }

  ngOnInit(): void {
    this.adminService.getAllLessons().subscribe({
      next: (data: Topic[]) => this.topics = data,
      error : () => this.showError('Erreur lors du chargement')
    });

    this.adminService.getAllUsers().subscribe({
      next: (data: User[]) => this.users = data,
      error : () => this.showError('Erreur lors du chargement')
    });

    this.adminService.getAllPurchases().subscribe({
      next: (data: Purchase[]) => this.purchases = data,
      error : () => this.showError('Erreur lors du chargement')
    });
  }

  toggleTopic(id: number): void {
    this.openedTopicId = this.openedTopicId === id ? null : id;
    this.openedCourseId = null;
  }

  toggleCourse(id: number): void {
    this.openedCourseId = this.openedCourseId === id ? null : id;
  }

  onEditTopic(topic: Topic): void {
    this.editingTopic = { ...topic };
  }

  onEditCourse(course: Course): void {
    const topic = this.topics.find(t => t.courses.some((c: Course) => c.id === course.id));
    this.editingCourse = {
      ...course, 
      topicId: topic?.id ?? 0
    };
  }

  onEditLesson(lesson: Lesson): void {
    const course = this.topics
      .flatMap(t => t.courses)
      .find(c => c.lessons.some(l => l.id === lesson.id));
    
    this.editingLesson = {
      ...lesson, 
      courseId: course?.id ?? 0
    };
  }

  onEditUser(user: User): void {
    this.editingUser = { ...user };
  }

  onEditPurchase(purchase: Purchase): void {
    this.editingPurchase = {
      ...purchase,
      customerId: purchase.customer.id,
      lessonId: purchase.lesson ? purchase.lesson.id : null,
      courseId: purchase.course ? purchase.course.id : null
    };

    this.purchaseType = purchase.lesson ? 'lesson' : (purchase.course ? 'course' : '');
  }

  cancelEdit(): void {
    this.editingTopic = null;
    this.editingCourse = null;
    this.editingLesson = null;
    this.editingUser = null;
    this.editingPurchase = null;
    this.purchaseType = '';
  }

  saveTopic(): void {
    if (!this.editingTopic) return;

    const { id, name } = this.editingTopic;

    this.adminService.updateTopic(id, { name }).subscribe({
      next: () => {
        const index = this.topics.findIndex(t => t.id === id);
        if (index !== -1) {
          this.topics[index].name = name;
        }
        this.editingTopic = null;
        this.showSuccess('Thème modifié avec succès !');
      },
      error: () => this.showError('Erreur lors de la modification')
    });
  }

  saveCourse(): void {
    if (!this.editingCourse) return;

    const { id, title, price, topicId } = this.editingCourse;

    this.adminService.updateCourse(id, { title, price, topicId }).subscribe({
      next: () => {
        for (const topic of this.topics) {
          const courseIndex = topic.courses.findIndex((c: Course) => c.id === id);
          if (courseIndex !== -1) {
            topic.courses.splice(courseIndex, 1);
          }
        }

        const targetTopic = this.topics.find(t => t.id === topicId);
        if (targetTopic) {
          targetTopic.courses.push({ id, title, price, lessons: [] });
        }

        this.editingCourse = null;
        this.showSuccess('Cursus modifié avec succès !');
      }, 
      error: () => this.showError('Erreur lors de la modification')
    });
  }

  saveLesson(): void {
    if (!this.editingLesson) return;

    const { id, title, price, content, videoUrl, courseId } = this.editingLesson;

    this.adminService.updateLesson(id, { id, title, price, content, videoUrl, courseId }).subscribe({
      next: () => {
        for (const topic of this.topics) {
          for (const course of topic.courses) {
            const index = course.lessons.findIndex(l => l.id === id);
            if (index !== -1) {
              course.lessons.splice(index, 1);
            }
          }
        }

        const targetCourse = this.topics
          .flatMap(t => t.courses)
          .find(c => c.id === courseId);

        if (targetCourse) {
          targetCourse.lessons.push({ id, title, price, content, videoUrl });
        }

        this.editingLesson = null;
        this.showSuccess('Leçon modifiée avec succès !');
      }, 
      error: () => this.showError('Erreur lors de la modification')
    });
  }

  saveUser(): void {
    if (!this.editingUser) return;

    const { id, name, email, roles, isVerified, password } = this.editingUser;
    const payload: any = { name, email, roles, isVerified };
    
    if (password?.trim()) {
      payload.password = password;
    }

    this.adminService.updateUser(id!, payload).subscribe({
      next: (updated) => {
        const index = this.users.findIndex(u => u.id === updated.id);
        if (index !== -1) this.users[index] = updated;
        this.editingUser = null;
        this.showSuccess('Utilisateur modifié avec succès !');
      }, 
      error: () => this.showError('Erreur lors de la modification')
    });
  }

  savePurchase(): void {
    if (!this.editingPurchase) return;

    const { id, customerId, lessonId, courseId } = this.editingPurchase;

    this.adminService.updatePurchase(id, { customerId, lessonId, courseId }).subscribe({
      next: (updated) => {
        const index = this.purchases.findIndex(p => p.id === updated.id);
        if (index !== -1) this.purchases[index] = updated;
        this.editingPurchase = null;
        this.showSuccess('Achat modifié avec succès !');
      }, 
      error: () => this.showError('Erreur lors de la modification')
    });
  }

  get allCourses(): (Course & { topicName: string })[] {
    return this.topics.flatMap((t: Topic) => 
      t.courses.map(c => ({ ...c, topicName: t.name }))
    );
  }

  get allLessons(): (Lesson & { courseTitle: string; topicName: string })[] {
    return this.topics.flatMap((t: Topic) => 
      t.courses.flatMap((c: Course) =>
        c.lessons.map((l: Lesson) => ({ ...l, courseTitle: c.title, topicName: t.name }))  
      )
    );
  }

  openDeleteConfirmation(type: 'topic' | 'course' | 'lesson' | 'user' | 'purchase', id: number): void {
    let name = '';

    if (type === 'topic') {
      const topic = this.topics.find(t => t.id === id);
      name = topic?.name ?? '';
    } else if (type === 'course') {
      const course = this.topics.flatMap(t => t.courses).find(c => c.id === id);
      name = course?.title ?? '';
    } else if (type === 'lesson') {
      const lesson = this.topics
        .flatMap(t => t.courses)
        .flatMap(c => c.lessons)
        .find(l => l.id === id);
      name = lesson?.title ?? '';
    } else if (type === 'user') {
      const user = this.users.find(u => u.id === id);
      name = user?.name ?? '';
    } else if (type === 'purchase') {
      const purchase = this.purchases.find(p => p.id === id);
      name = purchase ? purchase.customer.name : '';
    }

    this.confirmDelete = { type, id, name };
  }

  cancelDelete(): void {
    this.confirmDelete = null;
  }

  confirmDeleting(): void {
    if (!this.confirmDelete) return;

    const { type, id } = this.confirmDelete;

    const onSuccess = () => {
      if (type === 'topic') {
        this.topics = this.topics.filter(t => t.id !== id);
        this.showSuccess('Thème supprimé avec succès !');
      } else if (type === 'course') {
        for (const topic of this.topics) {
          topic.courses = topic.courses.filter(c => c.id !== id);
        }
        this.showSuccess('Cursus supprimé avec succès !');
      } else if (type === 'lesson') {
        for (const topic of this.topics) {
          for (const course of topic.courses) {
            course.lessons = course.lessons.filter(l => l.id !== id);
          }
        }
        this.showSuccess('Leçon supprimée avec succès !');
      } else if (type === 'user') {
        this.users = this.users.filter(u => u.id !== id);
        this.showSuccess('Utilisateur supprimé avec succès !');
      } else if (type === 'purchase') {
        this.purchases = this.purchases.filter(p => p.id !== id);
        this.showSuccess('Achat supprimé avec succès !');
      }

      this.confirmDelete = null;
    };

    const onError = () => {
      this.showError('Erreur lors de la suppression');
      this.confirmDelete = null;
    };

    if (type === 'topic') {
      this.adminService.deleteTopic(id).subscribe({ next: onSuccess, error: onError });
    } else if (type === 'course') {
      this.adminService.deleteCourse(id).subscribe({ next: onSuccess, error: onError });
    } else if (type === 'lesson') {
      this.adminService.deleteLesson(id).subscribe({ next: onSuccess, error: onError });
    } else if (type === 'user') {
      this.adminService.deleteUser(id).subscribe({ next: onSuccess, error: onError });
    } else if (type === 'purchase') {
      this.adminService.deletePurchase(id).subscribe({ next: onSuccess, error: onError });
    }
  }

  confirmAdd(): void {
    if (this.selectedAddType === 'topic') {
      this.adminService.addTopic(this.newTopic).subscribe({
        next: () => {
          this.showSuccess('Thème ajouté avec succès !');
          this.showAddForm = false;
        },
        error: () => this.showError("Erreur lors de l'ajout")
      });
    } else if (this.selectedAddType === 'course') {
      this.adminService.addCourse(this.newCourse).subscribe({
        next: () => {
          this.showSuccess('Cursus ajouté avec succès !');
          this.showAddForm = false;
        },
        error: () => this.showError("Erreur lors de l'ajout")
      });
    } else if (this.selectedAddType === 'lesson') {
      this.adminService.addLesson(this.newLesson).subscribe({
        next: () => {
          this.showSuccess('Leçon ajoutée avec succès !');
          this.showAddForm = false;
        },
        error: () => this.showError("Erreur lors de l'ajout")
      });
    } else if (this.showAddUserForm) {
      this.adminService.addUser(this.newUser).subscribe({
        next: () => {
          this.showSuccess('Utilisateur ajouté avec succès !');
          this.showAddUserForm = false;
        },
        error: () => this.showError("Erreur lors de l'ajout")
      });
    } else if (this.showAddPurchaseForm) {
      this.adminService.addPurchase(this.newPurchase).subscribe({
        next: () => {
          this.showSuccess('Achat ajouté avec succès !');
          this.showAddPurchaseForm = false;
        },
        error: () => this.showError("Erreur lors de l'ajout")
      });
    }
  }

  cancelAdd(): void {
    this.showAddForm = false;
    this.showAddUserForm = false;
    this.showAddPurchaseForm = false;
    this.selectedAddType = '';
    this.newTopic = { name: '' };
    this.newCourse = { title: '', price: 0, topicId: 0 };
    this.newLesson = { title: '', price: 0, content: '', videoUrl: '', courseId: 0 };
    this.newUser = { name: '', email: '', password: '', roles: [], isVerified: false };
    this.newPurchase = { customerId: 0 };
  }
}
